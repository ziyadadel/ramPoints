<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User_voutcher;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class UserVoutcherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userVouchers = User_voutcher::with('user', 'voucherPlan')->get()->sortByDesc('updated_at');

        $message = 'All User Vouchers';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode, 'data' => $userVouchers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'user_id' => 'required|integer||exists:users,id',
            'voucher_plan_id' => 'required|integer||exists:voutcher_plan,id',
            'value_in_pounds' => 'required|numeric|between:0,9999.99',
            'expiration_date' => 'required|date',
        ]);

        // Create a new user voucher instance
        $userVoucher = User_voutcher::create($validatedData);

        // Optionally, you can return a response indicating success or failure
        if ($userVoucher) {
            return response()->json(['message' => 'User voucher created successfully', 'user_voucher' => $userVoucher], 201);
        } else {
            return response()->json(['message' => 'Failed to create user voucher'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the user voucher by its id with relations loaded
        $userVoucher = User_voutcher::with('user', 'voucherPlan')->find($id);

        // Check if the user voucher exists
        if (!$userVoucher) {
            return response()->json(['message' => 'User voucher not found'], 404);
        }

        // Return the user voucher
        return response()->json(['user_voucher' => $userVoucher], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the user voucher by its id
        $userVoucher = User_voutcher::find($id);

        // Check if the user voucher exists
        if (!$userVoucher) {
            return response()->json(['message' => 'User voucher not found'], 404);
        }

        // Validate incoming request data
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'voucher_plan_id' => 'required|integer|exists:voutcher_plan,id',
            'value_in_pounds' => 'required|numeric|between:0,9999.99',
            'expiration_date' => 'required|date',
        ]);

        // Update the user voucher with validated data
        $userVoucher->update($validatedData);

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'User voucher updated successfully', 'user_voucher' => $userVoucher], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the user voucher by its id
        $userVoucher = User_voutcher::find($id);

        // Check if the user voucher exists
        if (!$userVoucher) {
            return response()->json(['message' => 'User voucher not found'], 404);
        }

        // Delete the user voucher
        $userVoucher->delete();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'User voucher deleted successfully'], 200);
    }

    /**
     * Update the status and sold_date of the specified resource.
     */
    public function updateStatusAndSoldDate($id)
    {
        $customerVoucher = User_voutcher::find($id);

        if (!$customerVoucher) {
            return response()->json(['message' => 'Customer Voucher not found'], 404);
        }

        // Update status to 0 and sold_date to current date
        $customerVoucher->status = 0;
        $customerVoucher->sold_date = Carbon::now()->toDateString();
        $customerVoucher->save();

        return response()->json(['message' => 'Status and sold_date updated successfully', 'customer_voucher' => $customerVoucher], 200);
    }

    /**
     * Search for customer vouchers by customer phone number.
     */
    public function searchByCustomerPhoneNumber(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'phone_number' => 'required|string', // Validate phone number
        ]);

        // Search for customer vouchers by customer phone number
        $customerVouchers = CustomerVoucher::whereHas('customer', function ($query) use ($validatedData) {
            $query->where('phone_number', $validatedData['phone_number']);
        })->get();

        if ($customerVouchers->isEmpty()) {
            return response()->json(['message' => 'No customer vouchers found for the provided phone number'], 404);
        }

        return response()->json(['customer_vouchers' => $customerVouchers], 200);
    }

}
