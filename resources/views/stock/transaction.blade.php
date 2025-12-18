<!DOCTYPE html>
<html>
<head>
    <title>Stock Transaction</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
</head>
<body>

<h2>Stock Transaction</h2>

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

    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Transaction Type</label>
        <div class="col-sm-6">
            <select name="type" class="form-control" required>
                <option value="">-- Choose --</option>
                <option value="IN">Add Stock</option>
                <option value="OUT">Reduce Stock</option>
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Product</label>
        <div class="col-sm-6">
            <select name="product_id" class="form-control" required>
                <option value="">-- Choose --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">
                        {{ $p->code }} - {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Location</label>
        <div class="col-sm-6">
            <select name="location_id" class="form-control" required>
                <option value="">-- Choose --</option>
                @foreach($locations as $l)
                    <option value="{{ $l->id }}">
                        {{ $l->code }} - {{ $l->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label">Quantity</label>
        <div class="col-sm-3">
            <input type="number" name="quantity" class="form-control" min="1" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="f-date" class="col-sm-3 col-form-label">
            Date (dd-mm-yyyy)
        </label>
        <div class="col-sm-3">
            <input
                type="text"
                id="f-date"
                name="transaction_date"
                class="form-control"
                placeholder="dd-mm-yyyy"
                autocomplete="off"
                required
            >
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-9 offset-sm-3">
            <button type="button" class="btn btn-primary" onclick="openConfirm()">
                Submit
            </button>
        </div>
    </div>
</form>


<div id="confirmModal" style="display:none; border:1px solid #000; padding:15px; background:#fff;">
    <h3>Confirm Transaction</h3>

    <p><strong>Type:</strong> <span id="c_type"></span></p>
    <p><strong>Product:</strong> <span id="c_product"></span></p>
    <p><strong>Location:</strong> <span id="c_location"></span></p>
    <p><strong>Quantity:</strong> <span id="c_quantity"></span></p>
    <p><strong>Date:</strong> <span id="c_date"></span></p>

    <button id="btn-submit" onclick="submitForm()">Confirm</button>

    <button onclick="closeConfirm()">Cancel</button>
</div>


<br>

<a href="{{ route('stock.saldo') }}">Balance Check</a> |
<a href="{{ route('stock.history') }}">History</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
function submitForm() {
    const btn = document.getElementById('btn-submit');

    btn.disabled = true;
    btn.innerText = 'Processing...';

    document.querySelector('form').submit();
}
</script>


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
        document.querySelector('[name="transaction_date"]').value;

    document.getElementById('confirmModal').style.display = 'block';
}

function closeConfirm() {
    document.getElementById('confirmModal').style.display = 'none';
}



</script>

<script>
$(function () {
    $('#f-date').datepicker({
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true
    });
});
</script>


</body>
</html>
