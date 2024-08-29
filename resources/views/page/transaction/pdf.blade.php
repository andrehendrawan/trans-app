<!DOCTYPE html>
<html>
<head>
    <title>Transaction PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table, .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>Transaction Details</h2>
    <table class="table">
        <tr>
            <th>ID</th>
            <td>{{ $transaction->id }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $transaction->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $transaction->email }}</td>
        </tr>
        <tr>
            <th>Product</th>
            <td>{{ $transaction->product }}</td>
        </tr>
        <tr>
            <th>Price</th>
            <td>{{ formatRupiah($transaction->price) }}</td>
        </tr>
        <tr>
            <th>Quantity</th>
            <td>{{ $transaction->quantity }}</td>
        </tr>
        <tr>
            <th>Total Price</th>
            <td>{{ formatRupiah($transaction->total_price) }}</td>
        </tr>
        <tr>
            <th>Issued By</th>
            <td>{{ $transaction->user->name }}</td>
        </tr>
    </table>
</body>
</html>
