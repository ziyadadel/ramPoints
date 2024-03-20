<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class transactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trans = Transaction::all();

        $message = 'All Transactions';
        $statusCode = Response::HTTP_OK;

        return response()->json(['message' => $message, 'status' => $statusCode,'data' => $trans]);
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
        $validator = Validator::make($request->all(), [
            'transaction_qr_code' => 'required|string|unique:transactions,transaction_qr_code',
            'transaction_date' => 'required|date',
            'transaction_number' => 'required|unique:transactions,transaction_number,NULL,id,branch_id,' . $request->input('branch_id'),
            'branch_id' => 'required|integer|exists:branchs,id',
            'number_of_points' => 'required|integer',
            'record_date' => 'date',
            'customer_id' => 'integer|exists:users,id',
            'created_at' => 'required|date_format:Y-m-d H:i:s',
            'updated_at' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Create a new transaction instance
            $transaction = Transaction::create($validator->validated());

            // Return a response indicating success
            return response()->json(['message' => 'created', 'transaction' => $transaction], 201);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($transaction_number, $branch_id)
    {
        // Fetch transactions by transaction_number and brunch_id, sorted by transaction_date
        $transactions = Transaction::where('transaction_number', $transaction_number)
                                    ->where('branch_id', $branch_id)
                                    ->orderBy('transaction_date', 'asc')
                                    ->get();

        // Check if any transactions were found
        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'No transactions found for the provided transaction number and store ID'], 404);
        }

        // Return the transactions
        return response()->json(['transactions' => $transactions], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the transaction by its id
        $transaction = Transaction::find($id);

        // Check if the transaction exists
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'transaction_qr_code' => 'string|unique:transactions,transaction_qr_code',
            'transaction_date' => 'date',
            'transaction_number' => 'unique:transactions,transaction_number,NULL,id,branch_id,' . $request->input('branch_id'),
            'branch_id' => 'integer|exists:branchs,id',
            'number_of_points' => 'integer',
            'record_date' => 'date',
            'customer_id' => 'integer|exists:users,id',
            'created_at' => 'date_format:Y-m-d H:i:s',
            'updated_at' => 'date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        

        // Update the magazine with validated data
        $transaction->update($validator->validated());

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Transaction updated successfully', 'transaction' => $transaction], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the transaction by its id
        $transaction = Transaction::find($id);

        // Check if the transaction exists
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Delete the transaction
        $transaction->delete();

        // Optionally, you can return a response indicating success or failure
        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }

    /**
     * Search for transactions by record_date and fetch every transaction on that day and the days after.
     */
    public function searchByRecordDate(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'record_date' => 'required|date', // Validate record_date
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert record_date to Carbon instance
            $recordDate = Carbon::parse($request->record_date);

            // Query transactions where record_date is on or after the provided record_date
            $transactions = Transaction::where('record_date', '>=', $recordDate->toDateTimeString())->get();

            return response()->json(['transactions' => $transactions], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to search transactions by record date', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search for transactions based on multiple criteria.
     */
    public function searchByAll(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                // Define validation rules for each search criterion
                'id' => 'integer',
                'transaction_qr_code' => 'string',
                'transaction_date' => 'date',
                'transaction_number' => 'integer',
                'branch_id' => 'integer',
                'number_of_points' => 'integer',
                'customer_id' => 'integer',
                'image' => 'string',
                'created_at' => 'date',
                'updated_at' => 'date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Get all input data from the request
            $searchData = $request->all();

            // Start building the query
            $query = Transaction::query();

            // Loop through each search criterion
            foreach ($searchData as $key => $value) {
                // Check if the key exists as a column in the transactions table
                if (in_array($key, Transaction::getTableColumns())) {
                    // Add a where clause for each search criterion
                    $query->where($key, $value);
                }
            }

            // Execute the query and fetch the transactions
            $transactions = $query->get();

            return response()->json(['transactions' => $transactions], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to search transactions', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function currentDate()
    {
        $currentDate = now();
        return response()->json($currentDate);
    }

    public function updateTransaction(Request $request)
    {

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'transaction_qr_code' => 'required|string',
            'customer_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Find the transaction based on the provided transaction_number
            $transaction = Transaction::where('transaction_qr_code', $request->input('transaction_qr_code'))->first();

            if (!$transaction) {
                $statusCode = Response::HTTP_NOT_FOUND;
                return response()->json(['message' => 'هذه الفاتورة غير موجودة','status' => $statusCode], 404);
            }

            // Check if customer_id is null and transaction_qr_code is provided
            if (is_null($transaction->customer_id) && $request->has('transaction_qr_code')) {
                
                    $transaction->customer_id = $request->customer_id;

                    // Set record_date to the current date and time
                    $transaction->record_date = Carbon::now();
        
                    // Save the updated transaction
                    $transaction->save();
                    
                    $user = User::where('id', $request->input('customer_id'))->first();
                    
                    $user->number_of_points = $transaction->number_of_points + $user->number_of_points;

                    // Save the updated user
                    $user->save();
                    $statusCode = Response::HTTP_OK;
        
                    // Return a response indicating success
                    return response()->json(['message' => 'تم إضافة النقاط ','status' => $statusCode ,'transaction' => $transaction], 200);
            }else{
                $statusCode = Response::HTTP_BAD_REQUEST;
                return response()->json(['message' => 'هذة الفاتوره مستخدمة من قبل ', 'status' => $statusCode], 400);
            }

        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function reserveBulk(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'transactions' => 'required|array',
            'transactions.*.transaction_qr_code' => 'required|string|unique:transactions,transaction_qr_code',
            'transactions.*.transaction_date' => 'required|date',
            'transactions.*.transaction_number' => 'required|unique:transactions,transaction_number,NULL,id,branch_id,' . $request->input('branch_id'),
            'transactions.*.branch_id' => 'required|integer|exists:branchs,id',
            'transactions.*.number_of_points' => 'required|integer',
            'transactions.*.record_date' => 'nullable|date',
            'transactions.*.customer_id' => 'nullable|integer|exists:users,id',
            'transactions.*.created_at' => 'required|date_format:Y-m-d H:i:s',
            'transactions.*.updated_at' => 'required|date_format:Y-m-d H:i:s',
        ]);
        // Validate the incoming request
        // $request->validate([
        //     'transactions' => 'required|array',
        //     'transactions.*' => $transactionValidationRules,
        // ]);
        // Check if validation fails for the current transaction
        if ($validator->fails()) {
            // Handle validation failure as per your requirement
            return response()->json(['error' => $validator->errors()], 400);
        }

        $reservedTransactions = [];
        $numStoredTransactions = 0;

        // Process each transaction in the request
        foreach ($request->input('transactions') as $transactionData) {
            

            

            // Create a new transaction instance
            $transaction = new Transaction();
            
            // Set transaction data
            $transaction->transaction_qr_code = $transactionData['transaction_qr_code'];
            $transaction->transaction_date = $transactionData['transaction_date'];
            $transaction->transaction_number = $transactionData['transaction_number'];
            $transaction->branch_id = $transactionData['branch_id'];
            $transaction->number_of_points = $transactionData['number_of_points'];
            $transaction->record_date = $transactionData['record_date'];
            $transaction->customer_id = $transactionData['customer_id'];
            $transaction->created_at = $transactionData['created_at'];
            $transaction->updated_at = $transactionData['updated_at'];
            // Set other properties as needed
            
            // Save the transaction
            $transaction->save();
            
            // Store the reservation
            $reservedTransactions[] = $transaction;
            
            // Increment the count of stored transactions
            $numStoredTransactions++;
        }

        return response()->json([
            'reserved_transactions' => $reservedTransactions,
            'num_stored_transactions' => $numStoredTransactions
        ]);
    }

}
