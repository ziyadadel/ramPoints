<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Voutcher_plan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Image;


class VoutcherPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $voucherPlans = Voutcher_plan::all();

        $message = 'All Voucher Plans';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode, 'data' => $voucherPlans]);
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
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|unique:voutcher_plan,id',
                'name' => 'required|string',
                'status' => 'required|integer',
                'number_of_points' => 'required|integer',
                'order_number' => 'required|integer',
                'value_in_pounds' => 'required|numeric|between:0,9999.99',
                'number_of_days_to_expire' => 'required|integer',
                'created_at' => 'date_format:Y-m-d H:i:s|required',
                'updated_at' => 'date_format:Y-m-d H:i:s|required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Check if a voucher plan with the given ID exists
            $voucherPlan = Voutcher_plan::find($request->id);

            if ($voucherPlan) {

                // Update voucher plan fields
                    $voucherPlan->name = $request->name;
                    $voucherPlan->number_of_points = $request->number_of_points;
                    $voucherPlan->order_number = $request->order_number;
                    $voucherPlan->value_in_pounds = $request->value_in_pounds;
                    $voucherPlan->number_of_days_to_expire = $request->number_of_days_to_expire;
                    $voucherPlan->status = $request->status;
                    $voucherPlan->created_at = $request->created_at;
                    $voucherPlan->updated_at = $request->updated_at;

                    // Handle image upload and deletion of old image
                    if ($request->hasFile('image')) {
                        $image = $request->file('image');
                        $imageName = time() . '.' . $image->getClientOriginalExtension();
                        $imagePath = '/img/voucherPlan_image/' . $imageName; // Set your image upload directory
                        $image->move(public_path('img/voucherPlan_image/'), $imageName); // Move image to public folder
                        
                        // Delete the old image if it exists
                        if ($voucherPlan->image && file_exists(public_path($voucherPlan->image))) {
                            unlink(public_path($voucherPlan->image));
                        }

                        // Update the image path in the database
                        $voucherPlan->image = $imagePath;
                    }

                    // Save the updated voucher plan
                    $voucherPlan->save();

                    // Optionally, you can return a response indicating success
                    return response()->json(['message' => 'Voucher plan updated successfully', 'voucher_plan' => $voucherPlan], 200);
            } else {
                // Handle image upload
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $imagePath = '/img/voucherPlan_image/' . $imageName; // Set your image upload directory
                    $image->move(public_path('img/voucherPlan_image/'), $imageName); // Move image to public folder
                    
                    // Create new voucher plan
                    $voucherPlan = Voutcher_plan::create([
                        'id' => $request->id,
                        'name' => $request->name,
                        'status' => $request->status,
                        'number_of_points' => $request->number_of_points,
                        'order_number' => $request->order_number,
                        'value_in_pounds' => $request->value_in_pounds,
                        'number_of_days_to_expire' => $request->number_of_days_to_expire,
                        'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at,
                        'image' => $imagePath,
                    ]);
                    
                    return response()->json(['message' => 'Voucher plan created successfully', 'voucher plan' => $voucherPlan], 201);
                } else {
                    return response()->json(['message' => 'Failed to create voucher without image'], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to process the request: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the voucher plan by its id
        $voucherPlan = Voutcher_plan::find($id);

        // Check if the voucher plan exists
        if (!$voucherPlan) {
            return response()->json(['message' => 'Voucher plan not found'], 404);
        }

        // Return the voucher plan
        return response()->json(['voucher_plan' => $voucherPlan], 200);
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
        try {
            // Find the voucher plan by ID
            $voucherPlan = Voutcher_plan::findOrFail($id);

            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'status' => 'required|integer',
                'number_of_points' => 'required|integer',
                'order_number' => 'required|integer',
                'value_in_pounds' => 'required|numeric|between:0,9999.99',
                'number_of_days_to_expire' => 'required|integer',
                'created_at' => 'date_format:Y-m-d H:i:s|required',
                'updated_at' => 'date_format:Y-m-d H:i:s|required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Update voucher plan fields
            $voucherPlan->name = $request->name;
            $voucherPlan->number_of_points = $request->number_of_points;
            $voucherPlan->order_number = $request->order_number;
            $voucherPlan->value_in_pounds = $request->value_in_pounds;
            $voucherPlan->number_of_days_to_expire = $request->number_of_days_to_expire;
            $voucherPlan->status = $request->status;
            $voucherPlan->created_at = $request->created_at;
            $voucherPlan->updated_at = $request->updated_at;

            // Handle image upload and deletion of old image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = '/img/voucherPlan_image/' . $imageName; // Set your image upload directory
                $image->move(public_path('img/voucherPlan_image/'), $imageName); // Move image to public folder
                
                // Delete the old image if it exists
                if ($voucherPlan->image && file_exists(public_path($voucherPlan->image))) {
                    unlink(public_path($voucherPlan->image));
                }

                // Update the image path in the database
                $voucherPlan->image = $imagePath;
            }

            // Save the updated voucher plan
            $voucherPlan->save();

            // Optionally, you can return a response indicating success
            return response()->json(['message' => 'Voucher plan updated successfully', 'voucher_plan' => $voucherPlan], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update voucher plan: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the voucher plan by its id
        $voucherPlan = Voutcher_plan::find($id);

        // Check if the voucher plan exists
        if (!$voucherPlan) {
            return response()->json(['message' => 'Voucher plan not found'], 404);
        }

        // Delete the voucher plan
        $voucherPlan->delete();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Voucher plan deleted successfully'], 200);
    }
}
