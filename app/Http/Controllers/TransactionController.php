<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('user')->get();
        return view('page.transaction.index', compact('transactions'));
    }

    public function create()
    {
        return view('page.transaction.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'product' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
        ]);

        $validated['total_price'] = $validated['price'] * $validated['quantity'];

        // Generate the custom ID
        $lastTransaction = Transaction::latest('created_at')->first();
        $lastId = $lastTransaction ? $lastTransaction->id : 'INV2024000000';
        $lastNumber = (int) substr($lastId, 3);
        $newNumber = str_pad($lastNumber + 1, 11, '0', STR_PAD_LEFT);
        $newId = 'INV' . $newNumber;

        $validated['id'] = $newId;

        $validated['user_id'] = Auth::id();

        if (!$validated['user_id']) {
            dd('User not authenticated.');
        }

        Transaction::create($validated);

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

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'product' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'quantity' => 'sometimes|integer',
        ]);

        if ($request->has('price') || $request->has('quantity')) {
            $price = $request->get('price', $transaction->price);
            $quantity = $request->get('quantity', $transaction->quantity);
            $validated['total_price'] = $price * $quantity;
        }

        $transaction->update($validated);

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
        
        $pdf = Pdf::loadView('page.transaction.pdf', compact('transaction'));

        return $pdf->download('transaction_' . $transaction->id . '.pdf');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
    
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                                    ->with('user')
                                    ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Product');
        $sheet->setCellValue('E1', 'Price');
        $sheet->setCellValue('F1', 'Quantity');
        $sheet->setCellValue('G1', 'Total Price');
        $sheet->setCellValue('H1', 'Issued By');
        $sheet->setCellValue('I1', 'Date');
        
        $row = 2;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, $transaction->id);
            $sheet->setCellValue('B' . $row, $transaction->name);
            $sheet->setCellValue('C' . $row, $transaction->email);
            $sheet->setCellValue('D' . $row, $transaction->product);
            $sheet->setCellValue('E' . $row, $transaction->price);
            $sheet->setCellValue('F' . $row, $transaction->quantity);
            $sheet->setCellValue('G' . $row, $transaction->total_price);
            $sheet->setCellValue('H' . $row, $transaction->user ? $transaction->user->name : 'No user');
            $sheet->setCellValue('I' . $row, $transaction->created_at->format('Y-m-d'));
            $row++;
        }
    
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
    
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="sales_report.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        return $response;
    }
    public function sendEmail($id)
    {
        $transaction = Transaction::findOrFail($id);

        $pdf = PDF::loadView('page.transaction.pdf', compact('transaction'));

        $pdfPath = storage_path('app/public/') . 'transaction_' . $transaction->id . '.pdf';
        $pdf->save($pdfPath);

        $data = [
            'name' => $transaction->name,
            'email' => $transaction->email,
            'transaction' => $transaction
        ];

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
