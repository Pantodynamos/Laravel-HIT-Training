<!DOCTYPE html>
<html>
<head>
    <title>History Transaksi</title>
    <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>
<body>

<h2>Report History Transaksi</h2>

<form method="GET" action="{{ route('stock.history') }}">
    <label>Reference:</label>
    <input type="text" name="reference" value="{{ request('reference') }}">

    <label>Tanggal:</label>
    <input type="text" name="transaction_date" placeholder="dd-mm-yyyy" value="{{ request('transaction_date') }}"> 

    <label>Product:</label>
    <input type="text" name="product" value="{{ request('product') }}">

    <label>Location:</label>
    <input type="text" name="location" value="{{ request('location') }}">

    <button type="submit">Cari</button>
</form>

<table border="1" cellpadding="6">
    <tr>
        <th>Reference</th>
        <th>Tanggal</th>
        <th>Qty</th>
        <th>Product</th>
        <th>Location</th>
    </tr>

    @foreach($report as $r)
        <tr>
            <td>{{ $r->reference }}</td>
            <td>{{ \Carbon\Carbon::parse($r->transaction_date)->format('d-m-Y') }}</td>
            <td>{{ $r->quantity }}</td>

            <td>
                {{ optional($r->stock->product)->name }}
            </td>

            <td>
                {{ optional($r->stock->location)->name }}
            </td>
        </tr>
        

    @endforeach
</table>

<br>    

<div>
{{ $report->links('pagination::bootstrap-4') }}

</div>

<br><br>

<a href="{{ route('stock.transaction.form') }}">Transaction</a> |
<a href="{{ route('stock.saldo') }}">Saldo</a>

</body>
</html>
