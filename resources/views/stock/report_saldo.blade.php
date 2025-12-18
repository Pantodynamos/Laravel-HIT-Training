<!DOCTYPE html>
<html>
<head>
    <title>Balance Check</title>
        <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<h2>Balance Check</h2>

<form method="GET" action="{{ route('stock.saldo') }}">
    <label>Product:</label>
    <select name="product_id" required>
        <option value="">-- Choose --</option>
        @foreach($products as $p)
            <option value="{{ $p->id }}"
                {{ request('product_id') == $p->id ? 'selected' : '' }}>
                {{ $p->code }} - {{ $p->name }}
            </option>
        @endforeach
    </select>

    <label>Location:</label>
    <select name="location_id" required>
        <option value="">-- Choose --</option>
        @foreach($locations as $l)
            <option value="{{ $l->id }}"
                {{ request('location_id') == $l->id ? 'selected' : '' }}>
                {{ $l->code }} - {{ $l->name }}
            </option>
        @endforeach
    </select>

    <button type="submit">Search</button>
</form>

<hr>

@if($saldo !== null && $stock->isNotEmpty())
    <h3>Product: {{ $stock->first()->product->name }}</h3>
    <h3>Location: {{ $stock->first()->location->name }}</h3>
    <h3>Total Saldo: {{ $saldo }}</h3>
@endif


<br>
<a href="{{ route('stock.transaction.form') }}">Transaction</a> |
<a href="{{ route('stock.history') }}">History</a>

</body>
</html>
