<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class InvoiceController extends Controller
{
    public function index()
    {
        try {
            return response()->json(Invoice::with(['customer', 'items'])->get(), 200);
        } catch (Exception $e) {
            Log::error('Error fetching invoices: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'done' => 'boolean',
                'created_by' => 'required|exists:users,id'
            ]);

            $invoice = Invoice::create($validated);
            return response()->json($invoice, 201);
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(['error' => $e->errors()], 422);
            }
            Log::error('Error creating invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function show(Invoice $invoice)
    {
        try {
            return response()->json($invoice->load('items'), 200);
        } catch (Exception $e) {
            Log::error('Error fetching invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'sometimes|exists:customers,id',
                'invoice_date' => 'sometimes|date',
                'done' => 'sometimes|boolean',
                'created_by' => 'sometimes|exists:users,id'
            ]);

            $invoice->update($validated);
            return response()->json($invoice, 200);
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                return response()->json(['error' => $e->errors()], 422);
            }
            Log::error('Error updating invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            $invoice->delete();
            return response()->json(['message' => 'Invoice deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getByCustomer($customer_id)
    {
        try {
            if (!Customer::where('id', $customer_id)->exists()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $invoices = Invoice::where('customer_id', $customer_id)
                ->with('items', 'creator:id,name', 'customer')
                ->paginate(10);

            return response()->json($invoices, 200);
        } catch (Exception $e) {
            Log::error('Error fetching invoices by customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
