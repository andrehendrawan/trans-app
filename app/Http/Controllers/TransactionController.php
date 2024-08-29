<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return view('page.transaction.index', compact('transactions'));
        // return csrf_token(); 
    }

    public function create()
    {
        return view('page.transaction.create');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'product' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
        ]);

        // Calculate total price
        $validated['total_price'] = $validated['price'] * $validated['quantity'];

        // Generate the custom ID
        $lastTransaction = Transaction::latest('created_at')->first();
        $lastId = $lastTransaction ? $lastTransaction->id : 'INV2024000000';
        $lastNumber = (int) substr($lastId, 3); // Extract the numeric part
        $newNumber = str_pad($lastNumber + 1, 11, '0', STR_PAD_LEFT); // Increment and pad number
        $newId = 'INV' . $newNumber;

        $validated['id'] = $newId; // Set the custom ID

        // Create the new transaction record
        Transaction::create($validated);

        // Redirect back to the transactions list with a success message
        return redirect()->route('transactions.index')->with('success', 'Transaction added successfully.');
    }


    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return view('page.transaction.show', compact('transaction'));
    }

    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);
        return view('page.transaction.edit', compact('transaction'));
    }


    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'product' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'quantity' => 'sometimes|integer',
        ]);

        // Check if price or quantity is being updated, and recalculate total_price if needed
        if ($request->has('price') || $request->has('quantity')) {
            $price = $request->get('price', $transaction->price); // Use new price or existing if not provided
            $quantity = $request->get('quantity', $transaction->quantity); // Use new quantity or existing if not provided
            $validated['total_price'] = $price * $quantity; // Recalculate total price
        }

        // Update the transaction with validated data
        $transaction->update($validated);

        // Redirect back with a success message
        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
    }


    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully!');
    }

    public function convertToPDF($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // Load the view and pass the transaction data to it
        $pdf = Pdf::loadView('page.transaction.pdf', compact('transaction'));
        // return dd($transaction);
        // Return the generated PDF for download
        return $pdf->download('transaction_' . $transaction->id . '.pdf');
    }
}
