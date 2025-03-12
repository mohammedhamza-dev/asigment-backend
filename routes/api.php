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

// Customer get Routes

Route::get('/customers/get', [CustomerController::class, 'Customers_get']); 
Route::get('/customer/{id}', [CustomerController::class, 'findById']); 



// Contract get  Routes
Route::get('/customers/{customer_id}/contracts', [ContractController::class, 'getContractsByCustomer']);
Route::get('/contracts/{id}', [ContractController::class, 'findById']); 

// Social Authentication Routes
Route::get('/google', [socialController::class, 'redirectToAuth']); 

// Invoice get  Routes
Route::get('/invoices/customer/{customer_id}', [InvoiceController::class, 'getByCustomer']); 

// Invoice  Item get Routes
Route::get('invoice-items/invoice/{invoice_id}', [InvoiceItemController::class, 'getByInvoiceId']); 

//auth routes
Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);


//post routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); 
    //customers Post
    Route::post('/customers/store', [CustomerController::class, 'Customers_store']); 
    Route::put('/customers/update/{customer}', [CustomerController::class, 'Customers_update']); 
    Route::delete('/customers/delete/{customer}', [CustomerController::class, 'Customers_destroy']); 

 //contracts Post
 Route::post('/contracts/store', [ContractController::class, 'contracts_store']); 
 Route::put('/contracts/update/{contract}', [ContractController::class, 'contracts_update']); 
 Route::delete('/contracts/delete/{contract}', [ContractController::class, 'contracts_destroy']); 

  //Invoice Post
  Route::post('/invoice/store', [InvoiceController::class, 'invoice_store']); 
  Route::put('/invoice/update/{invoice}', [InvoiceController::class, 'invoice_update']); 
  Route::delete('/invoice/delete/{invoice}', [InvoiceController::class, 'invoice_destroy']); 

  //Invoice Post
  Route::put('/invoice-items/update/{invoiceItem}', [InvoiceItemController::class, 'invoiceItems_update']); 
  Route::delete('/invoice-items/delete/{invoiceItem}', [InvoiceItemController::class, 'invoiceItems_destroy']); 

});
Route::post('/invoice-items/store', [InvoiceItemController::class, 'invoiceItems_store']); 
