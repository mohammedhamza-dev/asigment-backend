<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function index()
    {
        return InvoiceItem::with('invoice')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'item_name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $invoiceItem = InvoiceItem::create($validated);
        return response()->json($invoiceItem, 201);
    }

    public function show(InvoiceItem $invoiceItem)
    {
        return $invoiceItem->load('invoice');
    }

    public function update(Request $request, InvoiceItem $invoiceItem)
    {
        $invoiceItem->update($request->all());
        return response()->json($invoiceItem, 200);
    }

    public function destroy(InvoiceItem $invoiceItem)
    {
        $invoiceItem->delete();
        return response()->json(null, 204);
    }

    /**
     * Get all invoice items by invoice ID
     */
    public function getByInvoiceId($invoice_id)
    {
        $items = InvoiceItem::where('invoice_id', $invoice_id)->paginate(10);
        return response()->json($items);
    }
}
