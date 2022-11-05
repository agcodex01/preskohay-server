<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Http\Requests\OrderRequest;
use App\Http\Services\SmsService;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Order::all();
    }

    /**
     * Display a listing of the order by user
     *
     * @return \Illuminate\Http\Response
     */
    public function orderByUser($id)
    {
        $user = User::findOrFail($id);

        return $user->orders()->with('products')->get();
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

        $user->orders()->create();

        $order = $user->orders()->latest()->first();

        foreach($params as $data) {
            $order->products()
                ->syncWithoutDetaching([
                    $data['product_id'] => [
                        'quantity' => $data['quantity'],
                        'subtotal' => $data['subtotal'],
                    ]
                ]);
            $total += $data['subtotal'];
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

        return $order;
    }
}
