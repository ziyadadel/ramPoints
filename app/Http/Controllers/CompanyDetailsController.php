<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Company_details;
use Image;
use Illuminate\Support\Facades\Validator;

class CompanyDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyDetails = Company_details::all();

        $message = 'All Company Details';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode, 'data' => $companyDetails]);
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
            $validatedData = $request->validate([
                'id' => 'required|integer|unique:company_details,id',
                'company_name' => 'required|string|max:300',
                'created_at' => 'date_format:Y-m-d H:i:s|required',
                'updated_at' => 'date_format:Y-m-d H:i:s|required',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle image upload
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = '/img/company_logo/' . $imageName; // Set your image upload directory
                $image->move(public_path('img/company_logo/'), $imageName); // Move image to public folder
                // Create company detail
                $companyDetail = Company_details::create([
                    'id' => $request->id,
                    'company_name' => $request->company_name,
                    'logo' => $imagePath,
                    'created_at' => $request->created_at,
                    'updated_at' => $request->updated_at,
                ]);

                // Optionally, you can return a response indicating success
                return response()->json(['message' => 'Company detail created successfully', 'company_detail' => $companyDetail], 201);
            }else{
                return response()->json(['message' => 'Failed to create company without logo: '], 500);
            }

        } catch (QueryException $e) {
            // Handle database query exception
            return response()->json(['message' => 'Failed to create company detail: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'Failed to create company detail: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the company detail by its id
        $companyDetail = Company_details::find($id);

        // Check if the company detail exists
        if (!$companyDetail) {
            return response()->json(['message' => 'Company detail not found'], 404);
        }

        // Return the company detail
        return response()->json(['company_detail' => $companyDetail], 200);
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
            // Find the company detail by its id
            $companyDetail = Company_details::find($id);

            // Check if the company detail exists
            if (!$companyDetail) {
                return response()->json(['message' => 'Company detail not found'], 404);
            }

            // Validate incoming request data
            $validatedData = $request->validate([
                'company_name' => 'required|string|max:300',
                'created_at' => 'date_format:Y-m-d H:i:s|required',
                'updated_at' => 'date_format:Y-m-d H:i:s|required',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle image upload
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = '/img/company_logo/' . $imageName; // Set your image upload directory
                $image->move(public_path('img/company_logo/'), $imageName); // Move image to public folder

            // Delete old image if it exists
            if ($companyDetail->logo) {
                // Get the path of the old image
                $oldImagePath = public_path($companyDetail->logo);
                
                // Check if the old image exists before attempting deletion
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Delete the old image
                }
            }

                // Update company detail
                $companyDetail->update([
                    'company_name' => $request->company_name,
                    'logo' => $imagePath,
                    'created_at' => $request->created_at,
                    'updated_at' => $request->updated_at,
                ]);
            } else {
                // Update company detail
                $companyDetail->update([
                    'company_name' => $request->company_name,
                    'created_at' => $request->created_at,
                    'updated_at' => $request->updated_at,
                ]);
            }

            // Optionally, you can return a response indicating success
            return response()->json(['message' => 'Company detail updated successfully', 'company_detail' => $companyDetail], 200);
        } catch (QueryException $e) {
            // Handle database query exception
            return response()->json(['message' => 'Failed to update company detail: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'Failed to update company detail: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the company detail by its id
            $companyDetail = Company_details::find($id);

            // Check if the company detail exists
            if (!$companyDetail) {
                return response()->json(['message' => 'Company detail not found'], 404);
            }

            // Delete the image associated with the company detail if it exists
            if ($companyDetail->logo) {
                // Get the path of the image
                $imagePath = public_path($companyDetail->logo);
                
                // Check if the image file exists before attempting deletion
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Delete the image file
                }
            }

            // Delete the company detail
            $companyDetail->delete();

            // Optionally, you can return a response indicating success
            return response()->json(['message' => 'Company detail deleted successfully'], 200);
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['message' => 'Failed to delete company detail: ' . $e->getMessage()], 500);
        }
    }
}
