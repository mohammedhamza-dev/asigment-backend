<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InvoiceItemController extends Controller
{


    public function invoiceItems_store(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'item_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'subtotal' => 'required|numeric|min:0',
                'note' => 'nullable|string|max:500'
            ]);

            $invoiceItem = InvoiceItem::create($validated);
            return response()->json($invoiceItem, 201);
        }  catch (\Exception $e) {
            Log::error('Error creating invoice item: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create invoice item. Please try again later.'], 500);
        }
    }


    public function invoiceItems_update(Request $request, InvoiceItem $invoiceItem)
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'item_name' => 'nullable|string|max:255',
                'price' => 'nullable|numeric|min:0',
                'quantity' => 'nullable|integer|min:1',
                'subtotal' => 'nullable|numeric|min:0',
                'note' => 'nullable|string|max:500'
            ]);

            $invoiceItem->update($validated);
            return response()->json($invoiceItem, 200);
        } catch (\Exception $e) {
            Log::error('Error updating invoice item: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update invoice item. Please try again later.'], 500);
        }
    }

    public function invoiceItems_destroy(InvoiceItem $invoiceItem)
    {
        try {
            $invoiceItem->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting invoice item: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete invoice item. Please try again later.'], 500);
        }
    }

    /**
     * Get all invoice items by invoice ID
     */
    public function getByInvoiceId($invoice_id)
    {
        try {
            // Check if the invoice exists
            $invoiceExists = Invoice::where('id', $invoice_id)->exists();

            if (!$invoiceExists) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            // Fetch invoice items
            $items = InvoiceItem::where('invoice_id', $invoice_id)->paginate(10);

            return response()->json($items, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching invoice items by invoice ID: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve invoice items. Please try again later.'], 500);
        }
    }
}
