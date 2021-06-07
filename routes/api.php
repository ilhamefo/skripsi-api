<?php

use App\Http\Controllers\CartsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UsersController;
use App\Http\Resources\UserResource;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');


// middleware('auth:sanctum')->
Route::resource('/products', ProductsController::class)->except(['create', 'edit']);

Route::put('/user/update-avatar', [UsersController::class, 'updateAvatar']);
Route::delete('/user/update-avatar', [UsersController::class, 'deleteAvatar']);
Route::put('/user/update', [UsersController::class, 'update']);

Route::get('/verify-email/{id}/{hash}', [UsersController::class, 'verify'])->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::post('/user/updateEmail', [UsersController::class, 'updateEmail'])->middleware(['throttle:3,1']);
Route::post('/user/resetPassword', [UsersController::class, 'resetPassword']);

Route::prefix('/cart')->middleware('auth:sanctum')->group(function () {
    Route::post('/add', [CartsController::class, 'store']);
    Route::get('/view', [CartsController::class, 'show']);
    Route::get('/getTotal', [CartsController::class, 'getTotal']);
    Route::delete('/remove', [CartsController::class, 'destroy']);
});

Route::prefix('/product-category')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProductCategoryController::class, 'index']);
    Route::post('/', [ProductCategoryController::class, 'store']);
    Route::delete('/', [ProductCategoryController::class, 'destroy']);
    Route::put('/', [ProductCategoryController::class, 'update']);
});
Route::prefix('/employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::delete('/', [EmployeeController::class, 'destroy']);
    Route::put('/', [EmployeeController::class, 'update']);
});

Route::get('/test', function () {

    $user = User::findOrFail(1);
    $user->products;

    return $product = Products::findOrFail(4)->user;
});
