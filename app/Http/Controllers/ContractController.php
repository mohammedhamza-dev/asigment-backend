<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        return Contract::with(['customer', 'creator:id,name'])->get(); // Include creator's name
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after:start_date',
            'payment' => 'required|numeric',
            'note' => 'nullable|string',
            'created_by' => 'required|exists:users,id'
        ]);

        $contract = Contract::create($validated);
        return response()->json($contract->load(['customer', 'creator:id,name']), 201); // Load creator's name
    }

    public function show(Contract $contract)
    {
        return $contract->load(['customer', 'creator:id,name']); // Include creator's name
    }

    public function update(Request $request, Contract $contract)
    {
        $contract->update($request->all());
        return response()->json($contract->load(['customer', 'creator:id,name']), 200); // Include creator's name
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return response()->json(null, 204);
    }

    public function getContractsByCustomer($customer_id)
    {
        $contracts = Contract::where('customer_id', $customer_id)->with(['customer', 'creator:id,name'])->get();

        if ($contracts->isEmpty()) {
            return response()->json(['message' => 'لا توجد عقود لهذا العميل'], 404);
        }

        return response()->json($contracts, 200);
    }
}
