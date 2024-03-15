<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Offers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OffersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offers = Offers::all();

        $message = 'All Offers';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode, 'data' => $offers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'file' => 'required|mimes:pdf', // Validate PDF file
            'type' => 'required|in:1,2,3',
        ]);

        // Handle file upload
        $file = $request->file('file');

        // Define destination directory based on type
        $destinationDirectory = match ($request->type) {
            '1' => 'pdf/first',
            '2' => 'pdf/second',
            '3' => 'pdf/third',
            default => null,
        };

        if (!$destinationDirectory) {
            // Handle invalid type
            return response()->json(['message' => 'Invalid type provided'], 400);
        }

        // Define destination directory within the public folder
        $destinationPath = public_path($destinationDirectory);

        // Generate a unique filename for the uploaded file
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // Move the uploaded file to the destination directory
        $file->move($destinationPath, $filename);

        // Construct the file path for storage in the database
        $filePath = $destinationDirectory . '/' . $filename;

        // Find old offer record if it exists
        $oldOffer = Offers::where('type', $validatedData['type'])->first();

        // If old offer exists, delete its associated file
        if ($oldOffer && file_exists(public_path($oldOffer->file_path))) {
            unlink(public_path($oldOffer->file_path));
        }

        // Create a new offer instance or update the existing one
        if ($oldOffer) {
            $oldOffer->update([
                'file_path' => $filePath,
            ]);
            $offer = $oldOffer;
        } else {
            $offer = Offers::create([
                'file_path' => $filePath,
                'type' => $validatedData['type'],
            ]);
        }

        // Optionally, you can return a response indicating success or failure
        if ($offer) {
            return response()->json(['message' => 'Offer created successfully', 'offer' => $offer], 201);
        } else {
            return response()->json(['message' => 'Failed to create offer'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $offer = Offers::find($id);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        return response()->json(['offer' => $offer], 200);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        // Find the offer by ID
        $offer = Offers::findOrFail($id);

        // Validate incoming request data
        $validatedData = $request->validate([
            'file' => 'nullable|mimes:pdf', // Validate PDF file (optional)
            'type' => 'required|in:1,2,3',
        ]);

        // Handle file upload if a new file is provided
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Define destination directory based on type
            $destinationDirectory = match ($validatedData['type']) {
                '1' => 'pdf/first',
                '2' => 'pdf/second',
                '3' => 'pdf/third',
                default => null,
            };

            if (!$destinationDirectory) {
                // Handle invalid type
                return response()->json(['message' => 'Invalid type provided'], 400);
            }

            // Define destination directory within the public folder
            $destinationPath = public_path($destinationDirectory);

            // Generate a unique filename for the uploaded file
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Move the uploaded file to the destination directory
            $file->move($destinationPath, $filename);

            // Construct the file path for storage in the database
            $newFilePath = $destinationDirectory . '/' . $filename;

            // Find old offer record if it exists
            $oldOffer = Offers::where('type', $validatedData['type'])->first();

            // If old offer exists, delete its associated file
            if ($oldOffer && file_exists(public_path($oldOffer->file_path))) {
                unlink(public_path($oldOffer->file_path));
            }

            // Update the file path in the offer
            $offer->file_path = $newFilePath;
        }

        // Update the offer type
        $offer->type = $validatedData['type'];

        // Save the updated offer
        $offer->save();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Offer updated successfully', 'offer' => $offer], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the offer by ID
        $offer = Offers::find($id);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        // Delete offer file from storage
        if ($offer->file_path) {
            // Construct the full file path
            $filePath = public_path($offer->file_path);
            
            // Check if the file exists before attempting to delete it
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file
            } else {
                return response()->json(['message' => 'File not found'], 404);
            }
        }

        // Delete offer from database
        $offer->delete();

        return response()->json(['message' => 'Offer deleted successfully'], 200);
    }

}
