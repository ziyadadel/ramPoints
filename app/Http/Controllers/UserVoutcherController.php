<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User_voutcher;
use App\Models\User;
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
        $userVouchers = User_voutcher::with('user', 'voutcher_plan')->get()->sortByDesc('updated_at');

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
        try {
            $validator = Validator::make($request->all(),[
                'user_id' => 'required|integer|exists:users,id',
                'voutcher_plan_id' => 'required|integer|exists:voutcher_plan,id',
                'branch_id' => 'integer|exists:branchs,id',
                'value_in_pounds' => 'required|numeric|between:0,9999.99',
                'expiration_date' => 'required|date',
                'num_of_point' => 'required|integer',
                'status' => 'required|integer',
                'voutcher_plan_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                // Handle validation failure as per your requirement
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::where('id', $request->input('user_id'))->first();

            if($user->number_of_points >= $request->num_of_point)
            {
                $user->number_of_points =  $user->number_of_points - $request->num_of_point;

                // Save the updated user
                $user->save();

                $userVoucher = User_voutcher::create($validator->validated());
            }else{
                return response()->json(['message' => 'النقاط أكبر من المسموح'], 500);
            }
    
            // Create user voucher instance


                    

    
            // Optionally, you can return a response indicating success or failure
            if ($userVoucher) {
                return response()->json(['message' => 'User voucher created successfully', 'user_voucher' => $userVoucher], 201);
            } else {
                return response()->json(['message' => 'Failed to create user voucher'], 500);
            }
        } catch (QueryException $e) {
            // Handle database query exception
            return response()->json(['message' => 'Failed to create user voucher: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'Failed to create user voucher: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the user voucher by its id with relations loaded
        $userVoucher = User_voutcher::with('user', 'voutcher_plan')->find($id);

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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'voutcher_plan_id' => 'required|integer|exists:voutcher_plan,id',
            'branch_id' => 'integer|exists:branchs,id',
            'value_in_pounds' => 'required|numeric|between:0,9999.99',
            'expiration_date' => 'required|date',
            'num_of_point' => 'required|integer',
            'status' => 'required|integer',
            'voutcher_plan_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userVoucher->user_id = $request->user_id;
        $userVoucher->voutcher_plan_id = $request->voutcher_plan_id;
        $userVoucher->branch_id = $request->branch_id;
        $userVoucher->value_in_pounds = $request->value_in_pounds;
        $userVoucher->expiration_date = $request->expiration_date;
        $userVoucher->num_of_point = $request->num_of_point;
        $userVoucher->status = $request->status;
        $userVoucher->voutcher_plan_name = $request->voutcher_plan_name;

        // Update the user voucher with validated data
        $userVoucher->save();

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
        $customerVouchers = User_voutcher::whereHas('customer_id', function ($query) use ($validatedData) {
            $query->where('phone_number', $validatedData['phone_number']);
        })->get();

        if ($customerVouchers->isEmpty()) {
            return response()->json(['message' => 'No customer vouchers found for the provided phone number'], 404);
        }

        return response()->json(['customer_vouchers' => $customerVouchers], 200);
    }

    public function searchByDate(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'date' => 'required|date', // Validate record_date
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert record_date to Carbon instance
            $recordDate = Carbon::parse($request->date);

            // Query transactions where record_date is on or after the provided record_date
            $User_voutchers = User_voutcher::where('updated_at', '>=', $recordDate->toDateTimeString())->get();

            return response()->json(['users' => $User_voutchers], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to search user voucher by date', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateSoldDate(Request $request, $id)
    {
        // Find the user voucher by its id
        $userVoucher = User_voutcher::find($id);

        // Check if the user voucher exists
        if (!$userVoucher) {
            return response()->json(['message' => 'User voucher not found'], 404);
        }

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'sold_date' => 'required|date',
            'status' => 'required|integer',
            'branch_id' => 'required|integer|exists:branchs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userVoucher->sold_date = $request->sold_date;
        $userVoucher->status = $request->status;
        $userVoucher->branch_id = $request->branch_id;

        // Update the user voucher with validated data
        $userVoucher->save();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'User voucher updated successfully', 'user_voucher' => $userVoucher], 200);
    }

}
