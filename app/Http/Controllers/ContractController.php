<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ContractController extends Controller
{


    public function contracts_store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'start_date' => 'required|date',
                'expire_date' => 'required|date|after:start_date',
                'payment' => 'required|numeric',
                'note' => 'nullable|string',
                'created_by' => 'required|exists:users,id'
            ]);

            // Clean input to prevent XSS attacks
            if (isset($validated['note'])) {
                $validated['note'] = strip_tags($validated['note']);
            }


            $contract = Contract::create($validated);
            return response()->json(
                $contract->load(['customer', 'creator:id,name']),
                201
            );
        } catch (Exception $e) {
            Log::error('Error creating contract: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }



    public function contracts_update(Request $request, Contract $contract)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'sometimes|exists:customers,id',
                'start_date' => 'sometimes|date',
                'expire_date' => 'sometimes|date|after:start_date',
                'payment' => 'sometimes|numeric',
                'note' => 'nullable|string',
                'created_by' => 'sometimes|exists:users,id'
            ]);

            // Clean input to prevent XSS attacks
            if (isset($validated['note'])) {
                $validated['note'] = strip_tags($validated['note']);
            }

            $contract->update($validated);
            return response()->json(
                $contract->load(['customer', 'creator:id,name']),
                200
            );
        }  catch (Exception $e) {
            Log::error('Error updating contract: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function contracts_destroy($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->delete();
            return response()->json(['message' => 'Contract deleted successfully'], 200);
        }  catch (Exception $e) {
            Log::error('Error deleting contract: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function findById($id)
    {
        try {
            $contract = Contract::with(['customer', 'creator:id,name'])->findOrFail($id);
            return response()->json($contract, 200);
        } catch (Exception $e) {
            Log::error('Error finding contract: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getContractsByCustomer($customer_id)
    {
        try {
            if (!Customer::where('id', $customer_id)->exists()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $contracts = Contract::where('customer_id', $customer_id)
                ->with(['customer', 'creator:id,name'])
                ->paginate(10);

            return response()->json($contracts, 200);
        } catch (Exception $e) {
            Log::error('Error fetching contracts by customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
