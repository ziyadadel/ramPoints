<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Magazines;
use Illuminate\Support\Facades\Validator;

class MagazinesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $magazines = Magazines::all();

        $message = 'All Magazines';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode, 'data' => $magazines]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'id' => 'required|integer|unique:magazines,id',
            'magazine_url' => 'required|string|max:300',
            'active' => 'boolean',
            'created_at' => 'date_format:Y-m-d H:i:s|required',
            'updated_at' => 'date_format:Y-m-d H:i:s|required',
        ]);

        // Create a new magazine instance
        $magazine = Magazines::create($validatedData);

        // Optionally, you can return a response indicating success or failure
        if ($magazine) {
            return response()->json(['message' => 'Magazine created successfully', 'magazine' => $magazine], 201);
        } else {
            return response()->json(['message' => 'Failed to create magazine'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the magazine by its id
        $magazine = Magazines::find($id);

        // Check if the magazine exists
        if (!$magazine) {
            return response()->json(['message' => 'Magazine not found'], 404);
        }

        // Return the magazine
        return response()->json(['magazine' => $magazine], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the magazine by its id
        $magazine = Magazines::find($id);

        // Check if the magazine exists
        if (!$magazine) {
            return response()->json(['message' => 'Magazine not found'], 404);
        }

        // Validate incoming request data
        $validatedData = $request->validate([
            'magazine_url' => 'required|string|max:300',
            'active' => 'boolean',
        ]);

        // Update the magazine with validated data
        $magazine->update($validatedData);

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Magazine updated successfully', 'magazine' => $magazine], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the magazine by its id
        $magazine = Magazines::find($id);

        // Check if the magazine exists
        if (!$magazine) {
            return response()->json(['message' => 'Magazine not found'], 404);
        }

        // Delete the magazine
        $magazine->delete();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Magazine deleted successfully'], 200);
    }
}
