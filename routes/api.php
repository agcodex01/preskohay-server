<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\RecentSearchController;
use App\Http\Controllers\OrganizationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [LoginController::class, 'login'])->name('user.login');
Route::post('/user/register', [RegisterController::class, 'execute'])->name('customers.register');

Route::post('/drivers', [ApplicationController::class, 'store'])->name('register.driver');
Route::post('/drivers/license/{user}', [ApplicationController::class, 'storeApplicationLicense'])->name('drivers.license');
Route::put('/drivers/motor/{user}', [ApplicationController::class, 'storeApplicationMotor'])->name('drivers.motor');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', function () {
        $user = auth()->user();
        return [
            'user' => $user,
            'token' => null,
        ];
    });
    Route::apiResource('users', UserController::class)->except(['store']);
    Route::get('/user/receiver/{user}', [UserController::class, 'getUserById']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('user.logout');
    Route::get('/messages/{receiver_id}', [ChatController::class, 'fetchMessages']);
    Route::post('/message', [ChatController::class, 'sendMessage']);
    Route::get('/contact/list', [ChatController::class, 'getContactList']);
    Route::post('/pusher/auth', [ChatController::class, 'postAuth']);

    Route::apiResource('products', ProductController::class);
    Route::apiResource('posts', PostController::class);
    Route::get('/available-products', [ProductController::class, 'availableProductsToPost'])->name('products.to.posts');
    Route::post('/post/product/{post}', [PostController::class, 'storeByProducts'])->name('post.products');
    Route::post('/product/post/{post}', [PostController::class, 'productToPost'])->name('product.post');
    Route::delete('/post/product/{post}/{product}', [PostController::class, 'removeProduct'])->name('post.remove.product');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::post('/orders/store/{id}', [OrderController::class, 'store'])->name('order.product');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('order.show');
    Route::post('/orders/update/{order}', [OrderController::class, 'update'])->name('order.product.update');
    Route::post('/orders/update/{order}/add-driver', [OrderController::class, 'addDriver'])->name('order.product.addDriver');
    Route::post('/orders/update/{order}/mark-as-done', [OrderController::class, 'markAsDone'])->name('order.product.markAsDone');

    Route::get('/user-orders/{id}', [OrderController::class, 'orderByUser'])->name('order.user');
    Route::post('/user-orders/update/{order}', [OrderController::class, 'updateStatus'])->name('order.user.update');

    Route::apiResource('products', ProductController::class);
    Route::apiResource('posts', PostController::class);
    Route::get('/available-products', [ProductController::class, 'availableProductsToPost'])->name('products.to.posts');
    Route::post('/post/product/{post}', [PostController::class, 'storeByProducts'])->name('post.products');
    Route::post('/product/post/{post}', [PostController::class, 'productToPost'])->name('product.post');
    Route::delete('/post/product/{post}/{product}', [PostController::class, 'removeProduct'])->name('post.remove.product');

    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications');
    Route::get('/applications/list', [ApplicationController::class, 'list'])->name('applications.list');
    Route::post('/applications/confirm/{user}', [ApplicationController::class, 'confirm'])->name('applications.confirm');
    Route::post('/applications/done/{user}', [ApplicationController::class, 'done'])->name('applications.done');
    Route::post('/applications/declined/{user}', [ApplicationController::class, 'decline'])->name('application.declined');

    Route::get('/newsfeed', [PostController::class, 'newsFeed'])->name('post.newsfeed');
    Route::post('/newsfeed/search', [RecentSearchController::class, 'store'])->name('post.search');

    Route::apiResource('organizations', OrganizationController::class);

    Route::post('/update/password', [UserController::class, 'updatePassword'])->name('update.password');
    Route::get('/top-sales-products', [OrderController::class, 'orgDashboard'])->name('org.dashboard');

    Route::get('/recent-orders-places', [OrderController::class, 'displayOrdersInDriver'])->name('driver.dashboard');

});
