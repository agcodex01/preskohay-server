<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ApplicationRequest;
use App\Http\Implementations\SmsImplement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Order;
use Auth;

class ApplicationController extends Controller
{
    public function __construct(private SmsImplement $smsService)
    {

    }

    public function index()
    {
        return User::selectRaw('status, count(status) as total')
            ->where('user_role', 'driver')
            ->whereHas('application')
            ->groupBy('status')
            ->get();
    }

    public function getTopSales(Request $request)
    {
        $root_crops = $condiments = $vegetables = $fruits = $mixed = [];
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $orders = Order::where('status', config('const.order_status.delivered'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('products')
            ->get();

        foreach($orders as $order) {
            foreach($order->products as $product) {
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

        return $data;
    }

    public function getTopSalesFromCustomers(Request $request)
    {
        $data = [];
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $productQuery = Product::with(['orders' => function ($query) use ($year, $month) {
            return $query->whereYear('orders.created_at', $year)->whereMonth('orders.created_at', $month);
        }]);

        if ($request->isOrg) {
            $user = Auth::user();
            $productQuery->where('user_id', $user->id);
        }

        return $productQuery->get()
                ->groupBy('category')
                ->map(function ($category) {
                    return $category->map(function ($product) {
                        $product['total_customer_order'] = $product->orders->unique('user_id')->count();
                        return $product;
                    })->filter(function ($product) {
                        return $product->total_customer_order > 0;
                    });
                });
    }

    public function sort($category)
    {
        return $category->map(function ($value, $name) {
            $item['product_name'] = $name;
            $item['count'] = $value;

            return $item;
        })->take(5)->values()->all();
    }

    public function list()
    {
        return User::where('user_role', 'driver')
            ->whereHas('application')
            ->with('application')
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function confirm(User $user)
    {
        try {
            // Note: user number must start to 63
            $end_number = substr($user->contact_number, 1, 11);
            $number = '63'.$end_number;

            $message = $this->smsService
                ->to($number)
                ->message('Your application is already confirmed.')
                ->send();

            if ($message->getStatus() == 0) {
                $user->update([
                    'status' => 'in_progress'
                ]);

                return 'ALready confirmed';
            } else {
                return response([
                    'errors' => [
                        'sms' => $message->getStatus()
                    ]
                ], 422);
            }
        } catch (\Exception $th) {
            return response([
                'errors' => [
                    'sms' => $th->getMessage()
                ]
            ], 422);
        }
    }

    public function done(User $user)
    {
        return $user->update([
            'status' => 'done'
        ]);
    }

    public function decline(User $user)
    {
        return $user->update([
            'status' => 'declined'
        ]);
    }

    public function store(UserRequest $request)
    {
        $params = $request->validated();

        $params['password'] = Hash::make($params['password']);

        return User::create($params);
    }

    public function storeApplicationLicense(ApplicationRequest $request, User $user)
    {
        return $user->application()->create($request->validated());
    }

    public function storeApplicationMotor(ApplicationRequest $request, User $user)
    {
        return $user->application()->update($request->validated());
    }
}
