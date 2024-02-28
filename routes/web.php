<?php

use App\Http\Controllers\AdminBannerController;
use App\Http\Controllers\AdminBannerCoontroller;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCollectionController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Models\CartDetail;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        //'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/test', function () {
    // $products = Product::all();
    // foreach ($products as $product) {
    //     $product->slug = Str::of($product->name)->slug('-');
    //     $product->save();
    // }
    return CartDetail::all();
});

Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'laravel-filemanager'], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('admin/posts/list', [AdminPostController::class, 'list'])->name('admin.post.list');
    Route::get('admin/posts/add', [AdminPostController::class, 'add'])->name('admin.post.add.view');
    Route::post('admin/posts/store', [AdminPostController::class, 'store'])->name('admin.post.store');
    Route::post('admin/posts/action', [AdminPostController::class, 'action'])->name('admin.post.action');
    Route::get('admin/posts/edit/{id}', [AdminPostController::class, 'edit'])->name('admin.post.edit');
    Route::post('admin/posts/update', [AdminPostController::class, 'update'])->name('admin.post.update');

    Route::get('admin/banner', [AdminBannerController::class, 'list'])->name('admin.banner');


    Route::get('admin/reviews/list', [AdminReviewController::class, 'list'])->name('admin.review.list');
    Route::post('admin/reviews/update', [AdminReviewController::class, 'update'])->name('admin.review.update');
    Route::post('admin/reviews/action', [AdminReviewController::class, 'action'])->name('admin.review.action');

    Route::post('admin/collection/store', [AdminCollectionController::class, 'store'])->name('admin.collection.store');
    Route::get('admin/collection/add', [AdminCollectionController::class, 'add'])->name('admin.collection.add.view');
    Route::get('admin/collection', [AdminCollectionController::class, 'list'])->name('admin.collection.list');
    Route::post('admin/collection/update', [AdminCollectionController::class, 'update'])->name('admin.collection.update');
    Route::post('admin/collection/action', [AdminCollectionController::class, 'action'])->name('admin.collection.action');

    Route::get('admin/category/add', [AdminCategoryController::class, 'add'])->name('admin.category.add.view');
    Route::post('admin/category/store', [AdminCategoryController::class, 'store'])->name('admin.category.store');
    Route::get('admin/category/list', [AdminCategoryController::class, 'list'])->name('admin.category.list');
    Route::post('admin/category/update', [AdminCategoryController::class, 'update'])->name('admin.category.update');
    Route::post('admin/category/action', [AdminCategoryController::class, 'action'])->name('admin.category.action');

    Route::get('admin/product/list', [AdminProductController::class, 'list'])->name('admin.product.list');
    Route::get('/product/add', [AdminProductController::class, 'add'])->name('admin.product.add.view');
    Route::post('admin/product/store', [AdminProductController::class, 'store'])->name('admin.product.store');
    Route::post('admin/product/update', [AdminProductController::class, 'update'])->name('admin.product.update');
    Route::post('admin/product/action', [AdminProductController::class, 'action'])->name('admin.product.action');

    Route::get('admin/user/add', [AdminUserController::class, 'add'])->name('admin.user.add.view');
    Route::post('admin/user/store', [AdminUserController::class, 'store'])->name('admin.user.store');
    Route::get('admin/user/list', [AdminUserController::class, 'list'])->name('admin.user.list');
    Route::post('admin/user/update', [AdminUserController::class, 'update'])->name('admin.user.update');
    Route::post('admin/user/action', [AdminUserController::class, 'action'])->name('admin.user.action');

    Route::get('admin/order/list', [AdminOrderController::class, 'list'])->name('admin.order.list');
    Route::post('admin/order/action', [AdminOrderController::class, 'action'])->name('admin.order.action');
    Route::get('admin/order/edit', [AdminOrderController::class, 'edit'])->name('admin.order.edit');
    Route::post('admin/order/update', [AdminOrderController::class, 'update'])->name('admin.order.update');

   // Route::post('admin/order/update', [AdminDashboardController::class, 'pdf'])->name('admin.dashboard.pdf');

});

require __DIR__ . '/auth.php';
