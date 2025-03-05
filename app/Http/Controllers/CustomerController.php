<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        return Customer::with(['contracts', 'invoices'])->paginate(10);
    }
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'free_trial' => 'nullable|string',
            'start_date' => 'nullable|date',
            'note' => 'nullable|string',
            'created_by' => 'required|exists:users,id'
        ]);

        $customer = Customer::create($validated);
        return response()->json($customer, 201);
    }
    public function findById($id)
    {
        $customer = Customer::with(['contracts', 'invoices'])->find($id);
        
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        
        return response()->json($customer);
    }
    public function show(Customer $customer)
    {
        return $customer->load(['contracts', 'invoices']);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all());
        return response()->json($customer, 200);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}
