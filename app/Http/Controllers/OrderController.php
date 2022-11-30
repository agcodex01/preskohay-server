<?php

namespace App\Http\Controllers;

use App\Filters\OrderFilter;
use App\Models\User;
use App\Models\Order;
use App\Events\OrderEvent;
use App\Events\OrderDriverEvent;
use App\Http\Implementations\SmsImplement;
use App\Models\Product;
use App\Http\Requests\OrderRequest;
use App\Http\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(private SmsImplement $smsService)
    {

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrderFilter $filter)
    {
        return Order::filter($filter)->with('user', 'driver', 'products', 'farmer')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Display a listing of the order by user
     *
     * @return \Illuminate\Http\Response
     */
    public function orderByUser($id)
    {
        $user = User::findOrFail($id);

        return $user->orders()->with('products', 'farmer', 'user')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request, $id)
    {
        $total = 0;
        $user = User::findOrFail($id);

        $params = $request->validated();

        $user->orders()->create([
            'farmer_id' => $request->farmer_id,
            'drop_off' => $request->drop_off
        ]);

        $order = $user->orders()->latest()->first();

        event(new OrderEvent($order, $order->farmer_id));

        foreach($params['products'] as $data) {
            $order->products()
                ->syncWithoutDetaching([
                    $data['id'] => [
                        'quantity' => $data['quantity'],
                        'subtotal' => $data['sub_total'],
                    ]
                ]);
            $total += $data['sub_total'];
        }

        $order->update([
            'total' => $total,
        ]);

        return $order;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return $order;
    }

    /**
     * Update the shipping fee
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(OrderRequest $request, Order $order)
    {
        $params = $request->validated();

        $order->update([
            'total'         => $order->total + $params['shipping_fee'],
            'shipping_fee'  => $params['shipping_fee']
        ]);

        return $order;
    }

    /**
     * Update the status by user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(OrderRequest $request, Order $order)
    {
        $params = $request->validated();

        $order->update($params);

        event(new OrderEvent($order, $order->user->id));
        event(new OrderDriverEvent());

        return $order;
    }

    public function addDriver(Order $order, Request $request)
    {
        $order->update([
            'driver_id' => $request->driver_id
        ]);

        return $order->refresh();
    }

    public function markAsDone(Order $order, Request $request)
    {
        $order->update([
            'status' => 4
        ]);

        event(new OrderDriverEvent());

        return $order->refresh();
    }

    public function broadcastSMSDrivers(Order $order)
    {
        $drivers = User::where('user_role', 'driver')
            ->where('status', 'done')
            ->get();

        if ($drivers) {
            $drivers->each(function ($driver) use ($order) {
                // Note: user number must start to 63
                $end_number = substr($driver->contact_number, 1, 11);
                $number = '63'.$end_number;

                $message = 'There is a new Delivery Title POI-'.$order->id.' to '.
                    $order->drop_off.'. If you want to Deliver this order, please require as Driver.';

                $this->smsService->to($number)
                    ->message($message)
                    ->send();
            });

            return response([
                'success' => [
                    'sms' => 'Already send sms to drivers'
                ]
            ], 200);
        }

        return response([
            'errors' => [
                'drivers' => ['No drivers available']
            ]
        ], 422);
    }

    public function orgDashboard()
    {
        $user = Auth::user();
        $names = [];

        $orders = Order::where('farmer_id', $user->id)
            ->where('status', config('const.order_status.delivered'))
            ->with('products')
            ->get()
            ->each(function ($order) use (&$names) {
                $order->products->each(function($product) use (&$names) {
                    array_push($names, $product->name);
                });
            });

        $count = collect(array_count_values($names))->sortDesc();

        $products = $count->map(function ($value, $name) {
            $item['product_name'] = $name;
            $item['count'] = $value;

            return $item;
        });

        $data['products'] = $products->take(3)->values()->all();
        $data['summaryOrderPlaces'] = $this->placeDeliverSummery($user);

        return $data;
    }

    public function placeDeliverSummery($user)
    {
        $orders = Order::selectRaw('status, drop_off')
            ->where('farmer_id', $user->id)
            ->where(function ($query) {
                $query->where('status', config('const.order_status.delivered'))
                    ->orWhere('status', config('const.order_status.cancelled'));
            })
            ->get()
            ->groupBy('drop_off')
            ->map(function ($query, $ndx) {
                $confirmed = $cancelled = 0;

                $query->each(function ($query) use (&$confirmed, &$cancelled) {
                    $query->status == config('const.order_status.delivered') ? $confirmed++ : $cancelled++;
                });

                $item['name'] = $ndx;
                $item['confirmed'] = $confirmed;
                $item['cancelled'] = $cancelled;

                return $item;
            });

        return $orders->values()->all();
    }

    public function displayOrdersInDriver()
    {
        return Order::selectRaw('status, drop_off')
            ->where(function ($query) {
                $query->where('status', config('const.order_status.delivered'))
                    ->orWhere('status', config('const.order_status.cancelled'));
            })
            ->get()
            ->groupBy('drop_off')
            ->map(function ($query, $ndx) {
                $confirmed = $cancelled = 0;

                $query->each(function ($query) use (&$confirmed, &$cancelled) {
                    $query->status == config('const.order_status.delivered') ? $confirmed++ : $cancelled++;
                });

                $item['name'] = $ndx;
                $item['confirmed'] = $confirmed;
                $item['cancelled'] = $cancelled;

                return $item;
            })->values()->all();
    }
}
