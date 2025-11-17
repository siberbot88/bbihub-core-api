<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Http\Requests\Api\Customer\StoreCustomerRequest;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use Exception;

class CustomerApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return $this->successResponse('Success', $customers, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = Customer::create($request->validated());
            return $this->successResponse('Customer created successfully', $customer, 201);

        } catch (Exception $e) { // Tangkap error
            return $this->errorResponse('Failed to create customer, please try again.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return $this->successResponse('Customer found', $customer);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            $customer->update($request->validated());
            return $this->successResponse('Customer updated successfully', $customer);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update customer.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // 15. Gunakan Route-Model Binding
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return $this->successResponse('Customer deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete customer.', 500, $e->getMessage());
        }
    }
}
