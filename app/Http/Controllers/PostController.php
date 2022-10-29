<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\ProductRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Post::with('user')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $user = Auth::user();
        
        $params = $request->validated();

        $params['image'] = base64_encode(
            file_get_contents(
                $request->file('image')->path()
            )
        );

        $user->posts()->create($params);

        return $user->posts;
    }

    /**
     * Store a newly created post with products
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeByProducts(ProductRequest $request, Post $post)
    {
        $params = $request->validated();

        $post->products()->create($params);

        return $user->posts()->with('products')->get();
    }

    /**
     * Store a newly created product to post
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function productToPost(PostRequest $request, Post $post)
    {
        $params = $request->validated();

        foreach($params as $data) {
            $product = Product::findOrFail($data['id']);

            $product->update([
                'post_id' => $post->id
            ]);
        }

        return $post()->with('products')->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $post = $post
            ->whereHas('products', function ($query) {
                return $query->where('stocks', '>', 0);
            })
            ->with('products')
            ->get();
        
        return $post;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, Post $post)
    {
        $params = $request->validated();

        $params['image'] = base64_encode(
            file_get_contents(
                $request->file('image')->path()
            )
        );

        $post->update($params);

        return $post;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        return $post->delete();
    }
}
