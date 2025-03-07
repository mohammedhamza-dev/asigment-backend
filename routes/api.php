<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\socialController;

// Get the authenticated user's details
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); 

// Customer Routes
Route::apiResource('customers', CustomerController::class); 
Route::get('/customers/{customer_id}/contracts', [ContractController::class, 'getContractsByCustomer']);
Route::get('/customer/{id}', [CustomerController::class, 'findById']); 

// Social Authentication Routes
Route::get('/google', [socialController::class, 'redirectToAuth']); 

// Contract Routes
Route::apiResource('contracts', ContractController::class); 
Route::get('/contracts/{id}', [ContractController::class, 'findById']); 

// Invoice Routes
Route::apiResource('invoices', InvoiceController::class); // CRUD for invoices
Route::get('/invoices/customer/{customer_id}', [InvoiceController::class, 'getByCustomer']); 

// Invoice Item Routes
Route::apiResource('invoice-items', InvoiceItemController::class); 
Route::get('invoice-items/invoice/{invoice_id}', [InvoiceItemController::class, 'getByInvoiceId']); 

// Auth Routes - No authentication required
Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-verify-email', [AuthController::class, 'resendVerificationEmail']); 

// Protected Routes - Requires Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); 
});
