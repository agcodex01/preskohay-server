<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;

class OrderController extends Controller
{
    public $data = [];

    public function __construct()
    {
        $this->data['error'] = true;
        $this->data['message'] = 'Something went wrong.';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $user = Auth::user();
        $user = User::first();

        return $user->orders;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $user = Auth::user();
        $user = User::first();

        $user->orders()->create();

        return $user->orders;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {
        $total = 0;
        // $user = Auth::user();
        $user = User::first();

        $params = $request->validate();

        $this->create();

        foreach($params as $data) {
            $user->orders()->latest()->first()
                ->products()
                ->syncWithoutDetaching([
                    $data['product_id'] => [
                        'quantity' => $data['quantity'],
                        'subtotal' => $data['subtotal'],
                    ]
                ]);
            $total += $data['subtotal'];
        }

        $user->orders()->latest()->first()
            ->update([
                'total' => $total,
            ]);

        return $user->orders()->latest()->first();
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        return $order;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(OrderRequest $request, Order $order)
    {
        $params = $request->validated();

        $order->update($params);

        return $order;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
