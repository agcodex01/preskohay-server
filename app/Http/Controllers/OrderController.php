<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Log;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $user = Auth::user();
        $user = User::first();

        return $user->orders()->with('products')->get();
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

        return $user->orders()->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $total = 0;
            // $user = Auth::user();
            $user = User::first();

            $params = $request->validate();

            $this->create();

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

            DB::commit();

            $this->data['error']    = false;
            $this->data['message']  = 'Successfully Added Order.';
        } catch (\Exception $error) {
            DB::rollBack();
            Log::error($error->getMessage());
        }

        return $this->data;
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
       return $this->tempDelete($order);
    }
}
