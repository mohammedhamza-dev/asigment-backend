<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\socialController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('customers', CustomerController::class);
Route::apiResource('customers', CustomerController::class);
Route::get('/customers/{customer_id}/contracts', [ContractController::class, 'getContractsByCustomer']);


Route::get('/google', [socialController::class, 'redirectToAuth']);



Route::apiResource('contracts', ContractController::class);
Route::apiResource('invoices', InvoiceController::class);
Route::apiResource('invoice-items', InvoiceItemController::class);
Route::get('/invoices/customer/{customer_id}', [InvoiceController::class, 'getByCustomer']);
Route::get('invoice-items/invoice/{invoice_id}', [InvoiceItemController::class, 'getByInvoiceId']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-verify-email', [AuthController::class, 'resendVerificationEmail']);


// get all users
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
