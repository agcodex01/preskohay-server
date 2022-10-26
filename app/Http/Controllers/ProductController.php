<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        $data['products'] = Product::get();

        if (!isset($data['products'])) {
            $data['message'] = 'No Data Found.';
        }

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $data = [];
        $data['error'] = true;
        $data['message'] = 'Something went wrong.';

        $params = $request->all();

        $newProduct = Product::create($params);

        if ($newProduct) {
            $data['message'] = 'Successfully added new product!';
            $data['error'] = false;
        }

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = [];
        $data['error'] = true;
        $data['message'] = 'Something went wrong.';
        
        $params = $request->all();

        $updated = $product->update($params);

        if ($updated) {
            $data['error']   = false;
            $data['message'] = 'Successfully updated the product!';
        }

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $data = [];
        $data['error'] = false;
        $data['message'] = 'Successfully deleted';

        $deleted = $product->delete();

        if (!$deleted) {
            $data['error'] = true;
            $data['message'] = 'Something went wrong.';
        }

        return response()->json($data);
    }
}
