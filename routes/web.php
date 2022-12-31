<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Home\BasketController;
use App\Http\Controllers\Home\CheckoutController;
use App\Http\Controllers\Home\ProductsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\AdminOrdersController;
use App\Http\Controllers\Admin\AdminPaymentsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminProductsController;
use App\Http\Controllers\Admin\AdminCategoriesController;

Route::middleware('auth')->group(function () {
    
});

require __DIR__.'/auth.php';

Route::prefix('')->group(function (){
    Route::get('', [ProductsController::class, 'index'])->name('home.products.all');
    Route::get('{product_id}/show', [ProductsController::class, 'show'])->name('home.product.show');
    Route::get('{product_id}/addToBasket', [BasketController::class, 'addToBasket'])->name('home.basket.add');
    Route::get('{product_id}/removeFromBasket', [BasketController::class, 'removeFromBasket'])->name('home.basket.remove');
    Route::get('checkout', [CheckoutController::class, 'show'])->name('home.checkout');
});

Route::prefix('admin')->group(function (){
    
    Route::prefix('categories')->group(function (){
        Route::get('', [AdminCategoriesController::class, 'all'])->name('admin.categories.all');
        Route::get('create', [AdminCategoriesController::class, 'create'])->name('admin.categories.create');
        Route::post('', [AdminCategoriesController::class, 'store'])->name('admin.categories.store');
        Route::delete('{category_id}/delete', [AdminCategoriesController::class, 'delete'])->name('admin.categories.delete');
        Route::get('{category_id}/edit', [AdminCategoriesController::class, 'edit'])->name('admin.categories.edit');
        Route::put('{category_id}/update', [AdminCategoriesController::class, 'update'])->name('admin.categories.update');
    });

    Route::prefix('products')->group(function (){
        Route::get('', [AdminProductsController::class, 'all'])->name('admin.products.all');
        Route::get('create', [AdminProductsController::class, 'create'])->name('admin.products.create');
        Route::post('', [AdminProductsController::class, 'store'])->name('admin.products.store');
        Route::delete('{product_id}/delete', [AdminProductsController::class, 'delete'])->name('admin.products.delete');
        Route::get('{product_id}/edit', [AdminProductsController::class, 'edit'])->name('admin.products.edit');
        Route::put('{product_id}/update', [AdminProductsController::class, 'update'])->name('admin.products.update');

        Route::get('{product_id}/download/demo', [AdminProductsController::class, 'downloadDemo'])->name('admin.products.download.demo');
        Route::get('{product_id}/download/source', [AdminProductsController::class, 'downloadSource'])->name('admin.products.download.source');
    });

    Route::prefix('users')->group(function (){
        Route::get('', [AdminUsersController::class, 'all'])->name('admin.users.all');
        Route::get('create', [AdminUsersController::class, 'create'])->name('admin.users.create');
        Route::post('', [AdminUsersController::class, 'store'])->name('admin.users.store');
        Route::get('{user_id}/edit', [AdminUsersController::class, 'edit'])->name('admin.users.edit');
        Route::put('{user_id}/update', [AdminUsersController::class, 'update'])->name('admin.users.update');
        Route::delete('{user_id}/delete', [AdminUsersController::class, 'delete'])->name('admin.users.delete');
    });

    Route::prefix('orders')->group(function (){
        Route::get('', [AdminOrdersController::class, 'all'])->name('admin.orders.all');
    });

    Route::prefix('payments')->group(function (){
        Route::get('', [AdminPaymentsController::class, 'all'])->name('admin.payments.all');
    });

});

Route::prefix('payment')->group(function (){
    Route::post('pay', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::post('callback', [PaymentController::class, 'callback'])->name('payment.callback');
});
