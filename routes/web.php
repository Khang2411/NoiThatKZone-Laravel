<?php

use App\Http\Controllers\AdminBannerController;
use App\Http\Controllers\AdminBannerCoontroller;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCollectionController;
use App\Http\Controllers\AdminCouponController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


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

//Route::get('/test', [AdminDashboardController::class, 'pdf']);

Route::middleware('auth', 'checkRole')->group(function () {
    Route::group(['prefix' => 'laravel-filemanager'], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/statistic/pdf', [AdminDashboardController::class, 'pdf'])->name('admin.statistic.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('can:module.posts')->group(function () {
        Route::get('admin/posts/list', [AdminPostController::class, 'list'])->name('admin.post.list');
        Route::get('admin/posts/add', [AdminPostController::class, 'add'])->name('admin.post.add.view');
        Route::post('admin/posts/store', [AdminPostController::class, 'store'])->name('admin.post.store');
        Route::post('admin/posts/action', [AdminPostController::class, 'action'])->name('admin.post.action');
        Route::get('admin/posts/edit/{id}', [AdminPostController::class, 'edit'])->name('admin.post.edit');
        Route::post('admin/posts/update', [AdminPostController::class, 'update'])->name('admin.post.update');
        Route::post('admin/posts/delete/{id}', [AdminPostController::class, 'delete'])->name('admin.post.delete');
    });

    Route::middleware('can:module.banners')->group(function () {
        Route::get('admin/banner', [AdminBannerController::class, 'list'])->name('admin.banner.list');
        Route::post('admin/update', [AdminBannerController::class, 'update'])->name('admin.banner.update');
    });

    Route::middleware('can:module.reviews')->group(function () {
        Route::get('admin/reviews/list', [AdminReviewController::class, 'list'])->name('admin.review.list');
        Route::post('admin/reviews/update', [AdminReviewController::class, 'update'])->name('admin.review.update');
        Route::post('admin/reviews/action', [AdminReviewController::class, 'action'])->name('admin.review.action');
        Route::post('admin/reviews/delete/{id}', [AdminReviewController::class, 'delete'])->name('admin.review.delete');
    });

    Route::middleware('can:module.collections')->group(function () {
        Route::post('admin/collection/store', [AdminCollectionController::class, 'store'])->name('admin.collection.store');
        Route::get('admin/collection/add', [AdminCollectionController::class, 'add'])->name('admin.collection.add.view');
        Route::get('admin/collection', [AdminCollectionController::class, 'list'])->name('admin.collection.list');
        Route::post('admin/collection/update', [AdminCollectionController::class, 'update'])->name('admin.collection.update');
        Route::post('admin/collection/action', [AdminCollectionController::class, 'action'])->name('admin.collection.action');
        Route::post('admin/collection/delete/{id}', [AdminCollectionController::class, 'delete'])->name('admin.collection.delete');

        Route::get('admin/category/add', [AdminCategoryController::class, 'add'])->name('admin.category.add.view');
        Route::post('admin/category/store', [AdminCategoryController::class, 'store'])->name('admin.category.store');
        Route::get('admin/category/list', [AdminCategoryController::class, 'list'])->name('admin.category.list');
        Route::post('admin/category/update', [AdminCategoryController::class, 'update'])->name('admin.category.update');
        Route::post('admin/category/action', [AdminCategoryController::class, 'action'])->name('admin.category.action');
        Route::post('admin/category/delete/{id}', [AdminCategoryController::class, 'delete'])->name('admin.category.delete');
    });

    Route::middleware('can:module.coupons')->group(function () {
        Route::get('admin/coupon/list', [AdminCouponController::class, 'list'])->name('admin.coupon.list');
        Route::get('admin/coupon/add', [AdminCouponController::class, 'add'])->name('admin.coupon.add.view');
        Route::post('admin/coupon/store', [AdminCouponController::class, 'store'])->name('admin.coupon.store');
        Route::post('admin/coupon/update', [AdminCouponController::class, 'update'])->name('admin.coupon.update');
        Route::post('admin/coupon/action', [AdminCouponController::class, 'action'])->name('admin.coupon.action');
        Route::post('admin/coupon/delete/{id}', [AdminCouponController::class, 'delete'])->name('admin.coupon.delete');
    });

    Route::middleware('can:module.products')->group(function () {
        Route::get('admin/product/list', [AdminProductController::class, 'list'])->name('admin.product.list');
        Route::get('/product/add', [AdminProductController::class, 'add'])->name('admin.product.add.view');
        Route::post('admin/product/store', [AdminProductController::class, 'store'])->name('admin.product.store');
        Route::post('admin/product/update', [AdminProductController::class, 'update'])->name('admin.product.update');
        Route::post('admin/product/action', [AdminProductController::class, 'action'])->name('admin.product.action');
        Route::post('admin/product/delete/{id}', [AdminProductController::class, 'delete'])->name('admin.product.delete');
    });

    Route::middleware('can:module.orders')->group(function () {
        Route::get('admin/order/list', [AdminOrderController::class, 'list'])->name('admin.order.list');
        Route::post('admin/order/action', [AdminOrderController::class, 'action'])->name('admin.order.action');
        Route::get('admin/order/edit', [AdminOrderController::class, 'edit'])->name('admin.order.edit');
        Route::post('admin/order/update', [AdminOrderController::class, 'update'])->name('admin.order.update');
        Route::post('admin/order/delete/{id}', [AdminOrderController::class, 'delete'])->name('admin.order.delete');
    });

    Route::middleware('can:module.users')->group(function () {
        Route::get('admin/user/add', [AdminUserController::class, 'add'])->name('admin.user.add.view');
        Route::post('admin/user/store', [AdminUserController::class, 'store'])->name('admin.user.store');
        Route::get('admin/user/list', [AdminUserController::class, 'list'])->name('admin.user.list');
        Route::post('admin/user/update', [AdminUserController::class, 'update'])->name('admin.user.update');
        Route::post('admin/user/action', [AdminUserController::class, 'action'])->name('admin.user.action');
        Route::post('admin/user/delete/{id}', [AdminUserController::class, 'delete'])->name('admin.user.delete');
    });

    Route::middleware('can:module.customers')->group(function () {
        Route::post('admin/customer/store', [AdminCustomerController::class, 'store'])->name('admin.customer.store');
        Route::get('admin/customer/list', [AdminCustomerController::class, 'list'])->name('admin.customer.list');
        Route::post('admin/customer/update', [AdminCustomerController::class, 'update'])->name('admin.customer.update');
        Route::post('admin/customer/action', [AdminCustomerController::class, 'action'])->name('admin.customer.action');
        Route::post('admin/customer/delete/{id}', [AdminCustomerController::class, 'delete'])->name('admin.customer.delete');
    });

    Route::middleware('can:module.roles')->group(function () {
        Route::get('admin/role/add', [AdminRoleController::class, 'add'])->name('admin.role.add.view');
        Route::post('admin/role/store', [AdminRoleController::class, 'store'])->name('admin.role.store');
        Route::get('admin/role/list', [AdminRoleController::class, 'list'])->name('admin.role.list');
        Route::get('admin/role/edit/{id}', [AdminRoleController::class, 'edit'])->name('admin.role.edit');
        Route::post('admin/role/update/{id}', [AdminRoleController::class, 'update'])->name('admin.role.update');
        Route::post('admin/role/delete/{id}', [AdminRoleController::class, 'delete'])->name('admin.role.delete');
        Route::post('admin/role/action', [AdminRoleController::class, 'action'])->name('admin.role.action');
    });
});

require __DIR__ . '/auth.php';
