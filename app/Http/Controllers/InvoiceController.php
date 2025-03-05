<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return Invoice::with(['customer', 'items'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'done' => 'boolean',
            'created_by' => 'required|exists:users,id'
        ]);

        $invoice = Invoice::create($validated);
        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice)
    {
        return $invoice->load('items');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($request->all());
        return response()->json($invoice, 200);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(null, 204);
    }public function getByCustomer($customer_id)
    {
        $customerExists = Customer::where('id', $customer_id)->exists();
    
        if (!$customerExists) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    
        return Invoice::where('customer_id', $customer_id)
            ->with('items', 'creator:id,name')
            ->paginate(10);
    }
    
}
