<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            return response()->json(Customer::with(['contracts', 'invoices'])->paginate(10), 200);
        } catch (Exception $e) {
            Log::error('Error fetching customers: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'phone' => 'required|string',
                'free_trial' => 'nullable|string',
                'start_date' => 'nullable|date',
                'note' => 'nullable|string',
                'created_by' => 'required|exists:users,id'
            ]);

            // Sanitize the 'note' field
            if (isset($validated['note'])) {
                $validated['note'] = strip_tags($validated['note']);
            }

            $customer = Customer::create($validated);

            return response()->json($customer, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function findById($id)
    {
        try {
            $customer = Customer::with(['user'])->find($id);
            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return response()->json($customer, 200);
        } catch (Exception $e) {
            Log::error('Error finding customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function show(Customer $customer)
    {
        try {
            return response()->json($customer->load(['contracts', 'invoices']), 200);
        } catch (Exception $e) {
            Log::error('Error fetching customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string',
                'phone' => 'sometimes|string',
                'free_trial' => 'nullable|string',
                'start_date' => 'nullable|date',
                'note' => 'nullable|string',
                'created_by' => 'sometimes|exists:users,id'
            ]);

            // Sanitize the 'note' field if it exists
            if (isset($validated['note'])) {
                $validated['note'] = strip_tags($validated['note']);
            }

            $customer->update($validated);

            return response()->json($customer, 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return response()->json(['message' => 'Customer deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
