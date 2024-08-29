<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(Request $request)
    {
        // Validate the date inputs
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Retrieve transactions based on the date filter
        $transactions = Transaction::whereBetween('created_at', [$request->start_date, $request->end_date])->get();

        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header rows
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Product');
        $sheet->setCellValue('E1', 'Price');
        $sheet->setCellValue('F1', 'Quantity');
        $sheet->setCellValue('G1', 'Total Price');
        $sheet->setCellValue('H1', 'Date');

        // Fill data rows
        $row = 2; // Start from the second row
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, $transaction->id);
            $sheet->setCellValue('B' . $row, $transaction->name);
            $sheet->setCellValue('C' . $row, $transaction->email);
            $sheet->setCellValue('D' . $row, $transaction->product);
            $sheet->setCellValue('E' . $row, $transaction->price);
            $sheet->setCellValue('F' . $row, $transaction->quantity);
            $sheet->setCellValue('G' . $row, $transaction->total_price);
            $sheet->setCellValue('H' . $row, $transaction->created_at->format('Y-m-d'));
            $row++;
        }

        // Prepare response for download
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        // Set headers
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="sales_report.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function sendEmail($id)
    {
        // Fetch the transaction
        $transaction = Transaction::findOrFail($id);

        // Generate the PDF (assuming you are using DomPDF)
        $pdf = PDF::loadView('page.transaction.pdf', compact('transaction'));

        // Save the PDF temporarily
        $pdfPath = storage_path('app/public/') . 'transaction_' . $transaction->id . '.pdf';
        $pdf->save($pdfPath);

        // Prepare email data
        $data = [
            'name' => $transaction->name,
            'email' => $transaction->email,
            'transaction' => $transaction
        ];

        // Send email
        Mail::send('page.emails.transaction', $data, function ($message) use ($transaction, $pdfPath) {
            $message->to($transaction->email)
                    ->subject('Your Transaction Receipt')
                    ->attach($pdfPath);
        });

        // Delete the temporary PDF file
        unlink($pdfPath);

        return redirect()->back()->with('success', 'Email sent successfully to ' . $transaction->email);
    }
}
