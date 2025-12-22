<!DOCTYPE html>
<html>
<head>
    <title>Balance Check</title>

    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="p-3">

<h2>Balance Check</h2>

<div class="mb-3">
    <label>Product:</label>
    <select id="product_id" class="form-control d-inline-block w-auto">
        <option value="">-- Choose --</option>
        @foreach($products as $p)
            <option value="{{ $p->id }}">
                {{ $p->code }} - {{ $p->name }}
            </option>
        @endforeach
    </select>

    <label class="ml-3">Location:</label>
    <select id="location_id" class="form-control d-inline-block w-auto">
        <option value="">-- Choose --</option>
        @foreach($locations as $l)
            <option value="{{ $l->id }}">
                {{ $l->code }} - {{ $l->name }}
            </option>
        @endforeach
    </select>
</div>

<hr>

<div id="result-area" style="display:none;">
    <h3>Product: <span id="result-product"></span></h3>
    <h3>Location: <span id="result-location"></span></h3>
    <h3>Total Saldo: <span id="result-saldo"></span></h3>
</div>

<div id="empty-area">
    <em>Please select product and location</em>
</div>

<br>

<a href="{{ route('stock.transaction.form') }}">Transaction</a> |
<a href="{{ route('stock.history') }}">History</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadSaldo() {
    const productId  = $('#product_id').val();
    const locationId = $('#location_id').val();

    if (!productId || !locationId) {
        $('#result-area').hide();
        $('#empty-area').show();
        return;
    }

    $.ajax({
        url: "{{ route('stock.saldo.data') }}",
        method: 'GET',
        data: {
            product_id: productId,
            location_id: locationId
        },
        success: function (res) {
            if (res.stock.length === 0) {
                $('#result-area').hide();
                $('#empty-area').text('No data found').show();
                return;
            }

            const first = res.stock[0];

            $('#result-product').text(first.product.name);
            $('#result-location').text(first.location.name);
            $('#result-saldo').text(res.saldo);

            $('#empty-area').hide();
            $('#result-area').show();
        },
        error: function () {
            $('#result-area').hide();
            $('#empty-area').text('Failed to load saldo').show();
        }
    });
}

$('#product_id, #location_id').on('change', loadSaldo);
</script>

</body>
</html>
