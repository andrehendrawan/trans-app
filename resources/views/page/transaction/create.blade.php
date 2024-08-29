@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Add New Transaction</h2>

    <!-- Display validation errors, if any -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form to add a new transaction -->
    <form action="{{ route('transactions.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group mb-3">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group mb-3">
            <label for="product">Product:</label>
            <input type="text" class="form-control" id="product" name="product" value="{{ old('product') }}" required>
        </div>
        <div class="form-group mb-3">
            <label for="price">Price:</label>
            <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
        </div>
        <div class="form-group mb-3">
            <label for="quantity">Quantity:</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Transaction</button>
    </form>
</div>
@endsection
