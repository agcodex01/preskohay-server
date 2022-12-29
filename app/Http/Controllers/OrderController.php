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

        foreach ($params['products'] as $data) {
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

        $order->products()->each(function ($product) {

            $total = $product->stocks - $product->pivot->quantity;

            if ($product->pivot->quantity >= $product->stocks) {
                $total = 0;
            }

            $product->update([
                'stocks' => $total
            ]);
        });

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

        if ($params['status'] == config('const.order_status.on-the-way')) {
            $user = $order->user;

            $end_number = substr($user->contact_number, 1, 11);
            $number = '63' . $end_number;

            try {
                $this->smsService
                    ->to($number)
                    ->message('Hello ' . $user->last_name . ', ' . $user->first_name . '. Your order in Preskohay is now on the way.')
                    ->send();
            } catch (\Throwable $th) {
                return response([
                    'message' =>  "Vonage: " . $th->getMessage()
                ], 409);
            }
        }

        if ($params['status'] == config('const.order_status.cancelled')) {
            $order->products()->each(function ($product) {
                $product->update([
                    'stocks' => $product->stocks + $product->pivot->quantity
                ]);
            });
        }

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
        $driverOTWIds = Order::where('status', 1)
            ->whereNotNull('driver_id')
            ->pluck('driver_id');

        $drivers = User::where('user_role', 'driver')
            ->where('status', 'done')
            ->whereNotIn('id', $driverOTWIds)
            ->get();

        if ($drivers) {
            try {
                $drivers->each(function ($driver) use ($order) {
                    // Note: user number must start to 63
                    $end_number = substr($driver->contact_number, 1, 11);
                    $number = '63' . $end_number;

                    $message = 'There is a new Delivery Title POI-' . $order->id . ' to ' .
                        $order->drop_off . '. If you want to Deliver this order, please require as Driver.';

                    $this->smsService->to($number)
                        ->message($message)
                        ->send();
                });
            } catch (\Throwable $th) {
                return response([
                    'message' => "Vonage: " . $th->getMessage()
                ], 409);
            }


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

    public function orgDashboard(Request $request)
    {
        $user = Auth::user();

        $root_crops = $condiments = $vegetables = $fruits = $mixed = [];
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $orders = Order::where('farmer_id', $user->id)
            ->where('status', config('const.order_status.delivered'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('products')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                switch ($product['category']) {
                    case 'Root Crops':
                    case 'root crops':
                        array_push($root_crops, $product['name']);
                        break;
                    case 'Condiments':
                    case 'condiments':
                        array_push($condiments, $product['name']);
                        break;
                    case 'Vegetables':
                    case 'vegetables':
                        array_push($vegetables, $product['name']);
                        break;
                    case 'Fruits':
                    case 'fruits':
                        array_push($fruits, $product['name']);
                        break;
                    case 'mixed':
                        array_push($mixed, $product['name']);
                        break;
                }
            }
        }

        $mixed      = collect(array_count_values($mixed))->sortDesc();
        $fruits     = collect(array_count_values($fruits))->sortDesc();
        $root_crops = collect(array_count_values($root_crops))->sortDesc();
        $condiments = collect(array_count_values($condiments))->sortDesc();
        $vegetables = collect(array_count_values($vegetables))->sortDesc();

        $data['mixed']      = $this->sort($mixed);
        $data['fruits']     = $this->sort($fruits);
        $data['root_crops'] = $this->sort($root_crops);
        $data['condiments'] = $this->sort($condiments);
        $data['vegetables'] = $this->sort($vegetables);

        $data['summaryOrderPlaces'] = $this->placeDeliverSummery($user);

        return $data;
    }

    public function sort($category)
    {
        return $category->map(function ($value, $name) {
            $item['product_name'] = $name;
            $item['count'] = $value;

            return $item;
        })->take(5)->values()->all();
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

    public function topDriversListAdmin(Request $request)
    {
        $take = $request->take ?? 5;

        return Order::where(function ($query) {
            $query->where('status', config('const.order_status.delivered'))
                ->orWhere('status', config('const.order_status.cancelled'));
        })
            ->with('driver')
            ->whereHas('driver')
            ->get()
            ->groupBy('driver_id')
            ->map(function ($orders, $ndx) {
                $confirmed = $cancelled = 0;

                $orders->each(function ($order) use (&$confirmed, &$cancelled) {
                    $order->status == config('const.orders_status.delivered') ? $confirmed++ : $cancelled++;
                });
                $driver = $orders[0]->driver;
                $item['name'] = $driver->first_name . ' ' . $driver->last_name;
                $item['confirmed'] = $confirmed;
                $item['cancelled'] = $cancelled;

                return $item;
            })
            ->sortByDesc(function ($orders) {
                return $orders['confirmed'];
            })
            ->take($take)
            ->values()
            ->all();
    }

    public function topDriversListOrg(Request $request)
    {
        $take = $request->take ?? 5;

        $user = Auth::user();

        return Order::where('farmer_id', $user->id)
            ->where(function ($query) {
                $query->where('status', config('const.order_status.delivered'))
                    ->orWhere('status', config('const.order_status.cancelled'));
            })
            ->with('driver')
            ->whereHas('driver')
            ->get()
            ->groupBy('driver')
            ->map(function ($query, $ndx) {
                $confirmed = $cancelled = 0;

                $query->each(function ($query) use (&$confirmed, &$cancelled) {
                    $query->status == config('const.order_status.delivered') ? $confirmed++ : $cancelled++;
                });
                $driver = json_decode($ndx);
                $item['name'] = $driver->first_name . ' ' . $driver->last_name;
                $item['confirmed'] = $confirmed;
                $item['cancelled'] = $cancelled;

                return $item;
            })
            ->sortByDesc(function ($query) {
                return $query['confirmed'];
            })
            ->take($take)->values()->all();
    }
}
