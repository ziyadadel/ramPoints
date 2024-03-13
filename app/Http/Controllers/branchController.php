<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();

        $message = 'All Branches';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode, 'data' => $branches]);
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
            $validatedData = $request->validate([
                'id' => 'required|integer|unique:branchs,id',
                'name' => 'required|string|max:40',
                'address' => 'required|string|max:500',
                'created_at' => 'date_format:Y-m-d H:i:s|required',
                'updated_at' => 'date_format:Y-m-d H:i:s|required',
                'phone_number' => 'required|string'
            ]);

            // Create a new branch instance
            $branch = Branch::create($validatedData);

            // Optionally, you can return a response indicating success or failure
            if ($branch) {
                return response()->json(['message' => 'Branch created successfully', 'branch' => $branch], 201);
            } else {
                return response()->json(['message' => 'Failed to create branch'], 500);
            }
        } catch (\Exception $e) {
            // Handle the exception here, you can log it or return an error response
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the branch by its id
        $branch = Branch::find($id);

        // Check if the branch exists
        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        // Return the branch
        return response()->json(['branch' => $branch], 200);
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
        // Find the branch by its id
        $branch = Branch::find($id);
        // return response()->json($branch);

        // Check if the branch exists
        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:40',
            'address' => 'string|max:500',
            'created_at' => 'date_format:Y-m-d H:i:s',
            'updated_at' => 'date_format:Y-m-d H:i:s',
            'phone_number' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the branch with validated data
        $branch->update($validator->validated());

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Branch updated successfully', 'branch' => $branch], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the branch by its id
        $branch = Branch::find($id);

        // Check if the branch exists
        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        // Delete the branch
        $branch->delete();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Branch deleted successfully'], 200);
    }
}
