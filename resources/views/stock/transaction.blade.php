<!DOCTYPE html>
<html>
<head>
    <title>Stock Transaction</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>

<body class="p-3">

<h2>Stock Transaction</h2>

<div id="alert-area"></div>

<form id="transaction-form">

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
        <label class="col-sm-3 col-form-label">Date</label>
        <div class="col-sm-3">
            <input type="text"
                   id="f-date"
                   name="transaction_date"
                   class="form-control"
                   autocomplete="off"
                   required>
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

<div id="confirmModal"
     style="display:none; border:1px solid #000; padding:15px; background:#fff;">
    <h3>Confirm Transaction</h3>

    <p><strong>Type:</strong> <span id="c_type"></span></p>
    <p><strong>Product:</strong> <span id="c_product"></span></p>
    <p><strong>Location:</strong> <span id="c_location"></span></p>
    <p><strong>Quantity:</strong> <span id="c_quantity"></span></p>
    <p><strong>Date:</strong> <span id="c_date"></span></p>

    <button id="btn-submit" class="btn btn-success" onclick="submitAjax()">
        Confirm
    </button>

    <button class="btn btn-secondary" onclick="closeConfirm()">
        Cancel
    </button>
</div>

<br>

<a href="{{ route('stock.saldo') }}">Balance Check</a> |
<a href="{{ route('stock.history') }}">History</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
function showAlert(type, message) {
    $('#alert-area').html(
        `<div class="alert alert-${type}">${message}</div>`
    );
}

function submitAjax() {
    const btn = $('#btn-submit');
    btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: "{{ route('stock.transaction.submit') }}",
        method: 'POST',
        data: $('#transaction-form').serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            showAlert('success',
                `Transaction saved. Reference: <strong>${res.reference}</strong>`
            );

            $('#transaction-form')[0].reset();
            closeConfirm();
        },
        error: function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                let msg = '<ul>';

                $.each(xhr.responseJSON.errors, function (_, err) {
                    msg += `<li>${err[0]}</li>`;
                });

                msg += '</ul>';
                showAlert('danger', msg);
            } else {
                showAlert('danger', 'Transaction failed');
            }
        },
        complete: function () {
            btn.prop('disabled', false).text('Confirm');
        }
    });
}

function openConfirm() {
    const form = document.getElementById('transaction-form');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    $('#c_type').text(
        $('[name="type"]').val() === 'IN' ? 'ADD STOCK' : 'REDUCE STOCK'
    );
    $('#c_product').text(
        $('[name="product_id"] option:selected').text()
    );
    $('#c_location').text(
        $('[name="location_id"] option:selected').text()
    );
    $('#c_quantity').text($('[name="quantity"]').val());
    $('#c_date').text($('[name="transaction_date"]').val());

    $('#confirmModal').show();
}

function closeConfirm() {
    $('#confirmModal').hide();
}

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
