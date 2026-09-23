<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProfileController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
        Route::middleware('permission:clients.view')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
    });
    Route::middleware('permission:clients.create')->post('/clients', [ClientController::class, 'store']);
    Route::middleware('permission:clients.edit')->put('/clients/{client}', [ClientController::class, 'update']);
    Route::middleware('permission:clients.delete')->delete('/clients/{client}', [ClientController::class, 'destroy']);

    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
    });
    Route::middleware('permission:products.create')->post('/products', [ProductController::class, 'store']);
    Route::middleware('permission:products.edit')->put('/products/{product}', [ProductController::class, 'update']);
    Route::middleware('permission:products.delete')->delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::middleware('permission:products.view')->get('/stock-movements', [StockMovementController::class, 'index']);
    Route::middleware('permission:stock.manage')->post('/stock-movements', [StockMovementController::class, 'store']);


    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    });
    Route::middleware('permission:invoices.create')->post('/invoices', [InvoiceController::class, 'store']);
    Route::middleware('permission:invoices.edit')->group(function () {
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::post('/invoices/{invoice}/envoyer', [InvoiceController::class, 'envoyer']);
    });
    Route::middleware('permission:invoices.delete')->delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
    Route::middleware('permission:invoices.cancel')->post('/invoices/{invoice}/annuler', [InvoiceController::class, 'annuler']);

    Route::middleware('permission:payments.view')->get('/invoices/{invoice}/payments', [PaymentController::class, 'index']);
    Route::middleware('permission:payments.create')->post('/invoices/{invoice}/payments', [PaymentController::class, 'store']);
    Route::middleware('permission:payments.delete')->delete('/payments/{payment}', [PaymentController::class, 'destroy']);

    Route::middleware('permission:invoices.download')->get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
        Route::get('/dashboard/simple', [DashboardController::class, 'simple']);
    });

    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });
    Route::middleware('permission:users.create')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:users.edit')->group(function () {
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}/toggle-actif', [UserController::class, 'toggleActif']);
    });
    Route::middleware('permission:users.delete')->delete('/users/{user}', [UserController::class, 'destroy']);

    Route::middleware('auth:sanctum')->group(function () {
        // ... routes existantes (logout, me, clients, etc.)
        Route::put('/me', [ProfileController::class, 'update']);
    });
});