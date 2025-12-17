<!DOCTYPE html>
<html>
<head>
    <title>Transaksi Stok</title>
        <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<h2>Transaksi Stok (Tambah / Kurangi)</h2>

@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

@if ($errors->any())
    <ul style="color:red">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('stock.transaction.submit') }}">
    @csrf

    <label>Jenis Transaksi:</label>
    <select name="type" required>
        <option value="">-- Pilih --</option>
        <option value="IN">Tambah Stok</option>
        <option value="OUT">Kurangi Stok</option>
    </select>
    <br><br>

    <label>Produk:</label>
    <select name="product_id" required>
        @foreach($products as $p)
            <option value="{{ $p->id }}">
                {{ $p->code }} - {{ $p->name }}
            </option>
        @endforeach
    </select>
    <br><br>

    <label>Lokasi:</label>
    <select name="location_id" required>
        @foreach($locations as $l)
            <option value="{{ $l->id }}">
                {{ $l->code }} - {{ $l->name }}
            </option>
        @endforeach
    </select>
    <br><br>

    <label>Quantity:</label>
    <input type="number" name="quantity" min="1" required>
    <br><br>

    <label>Tanggal (dd-mm-yyyy):</label>
    <input type="text" name="date" placeholder="dd-mm-yyyy" required>
    <br><br>

    <button type="button" onclick="openConfirm()">Submit</button>
</form>

<div id="confirmModal" style="display:none; border:1px solid #000; padding:15px; background:#fff;">
    <h3>Confirm Transaction</h3>

    <p><strong>Type:</strong> <span id="c_type"></span></p>
    <p><strong>Product:</strong> <span id="c_product"></span></p>
    <p><strong>Location:</strong> <span id="c_location"></span></p>
    <p><strong>Quantity:</strong> <span id="c_quantity"></span></p>
    <p><strong>Date:</strong> <span id="c_date"></span></p>

    <button onclick="submitForm()">Confirm</button>
    <button onclick="closeConfirm()">Cancel</button>
</div>


<br>

<a href="{{ route('stock.saldo') }}">Cek Saldo</a> |
<a href="{{ route('stock.history') }}">History</a>

<script>
document.addEventListener('input', function (e) {
    if (e.target.name === 'quantity') {
        let value = parseInt(e.target.value, 10);

        if (isNaN(value) || value < 1) {
            e.target.value = '';
        } else {
            e.target.value = value;
        }
    }
});

function openConfirm() {
    const form = document.querySelector('form');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    document.getElementById('c_type').innerText =
        document.querySelector('[name="type"]').value === 'IN'
            ? 'ADD STOCK'
            : 'REDUCE STOCK';

    document.getElementById('c_product').innerText =
        document.querySelector('[name="product_id"] option:checked').text;

    document.getElementById('c_location').innerText =
        document.querySelector('[name="location_id"] option:checked').text;

    document.getElementById('c_quantity').innerText =
        document.querySelector('[name="quantity"]').value;

    document.getElementById('c_date').innerText =
        document.querySelector('[name="date"]').value;

    document.getElementById('confirmModal').style.display = 'block';
}

function closeConfirm() {
    document.getElementById('confirmModal').style.display = 'none';
}

function submitForm() {
    document.querySelector('form').submit();
}
</script>

</body>
</html>
