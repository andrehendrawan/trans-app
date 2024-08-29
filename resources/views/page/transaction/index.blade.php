@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Transactions</h2>

    <!-- Display success message, if any -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('transactions.create') }}" class="btn btn-success">Add New Transaction</a>
        <!-- Form for converting to Excel -->
        <form id="excel-form" action="{{ route('transactions.export') }}" method="GET">
            <!-- Add inputs for the date filters -->
            <input type="date" name="start_date" id="start_date" required>
            <input type="date" name="end_date" id="end_date" required>
            <button type="submit" class="btn btn-info">Convert to Excel</button>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $transaction->id }}</td>
                        <td>{{ $transaction->name }}</td>
                        <td>{{ $transaction->email }}</td>
                        <td>{{ $transaction->product }}</td>
                        <td>{{ formatRupiah($transaction->price)}}</td>
                        <td>{{ $transaction->quantity }}</td>
                        <td>{{ formatRupiah($transaction->total_price) }}</td>
                        <td>
                            <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                            <a href="{{ route('transactions.convertToPDF', $transaction->id) }}" class="btn btn-sm btn-warning">Convert PDF</a>
                            <form action="{{ route('transactions.email', $transaction->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info">Send Email</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
