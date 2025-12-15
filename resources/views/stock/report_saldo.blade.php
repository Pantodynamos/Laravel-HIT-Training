<!DOCTYPE html>
<html>
<head>
    <title>Report Saldo</title>
        <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<h2>Report Saldo Barang</h2>

<form method="GET" action="{{ route('stock.saldo') }}">
    <label>Produk:</label>
    <select name="product_id" required>
        <option value="">-- Pilih --</option>
        @foreach($products as $p)
            <option value="{{ $p->id }}"
                {{ request('product_id') == $p->id ? 'selected' : '' }}>
                {{ $p->code }} - {{ $p->name }}
            </option>
        @endforeach
    </select>

    <label>Lokasi:</label>
    <select name="location_id" required>
        <option value="">-- Pilih --</option>
        @foreach($locations as $l)
            <option value="{{ $l->id }}"
                {{ request('location_id') == $l->id ? 'selected' : '' }}>
                {{ $l->code }} - {{ $l->name }}
            </option>
        @endforeach
    </select>

    <button type="submit">Cari</button>
</form>

<hr>

@if(!is_null($saldo))
    <h3>Hasil Saldo</h3>

    @if($stock)
        <p>
            <strong>Produk:</strong>
            {{ $stock->product->code }} - {{ $stock->product->name }}
        </p>

        <p>
            <strong>Lokasi:</strong>
            {{ $stock->location->code }} - {{ $stock->location->name }}
        </p>

        <p>
            <strong>Total Saldo:</strong>
            {{ $saldo }}
        </p>
    @else
        <p><strong>Saldo:</strong> 0</p>
        <p>Data stok tidak ditemukan untuk kombinasi produk dan lokasi ini.</p>
    @endif
@endif

<br>
<a href="{{ route('stock.transaction.form') }}">Transaction</a> |
<a href="{{ route('stock.history') }}">History</a>

</body>
</html>
