<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <title>History Transaksi</title>
</head>
<body>

<h2>Report History Transaksi</h2>

<br>

<div style="margin-bottom: 10px;">
    <input type="text" id="f-reference" placeholder="Reference">

    <input type="text" id="f-date" placeholder="dd-mm-yyyy">

    <input type="text" id="f-product" placeholder="Product">

    <input type="text" id="f-location" placeholder="Location">

    <button id="btn-filter">Search</button>
</div>


<table id="history-table" class="display">
    <thead>
        <tr>
            <th>Reference</th>
            <th>Tanggal</th>
            <th>Qty</th>
            <th>Product</th>
            <th>Location</th>
        </tr>
    </thead>
</table>

<br>
<a href="{{ route('stock.transaction.form') }}">Transaction</a> |
<a href="{{ route('stock.saldo') }}">Saldo</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(function () {

    let table = $('#history-table').DataTable({
        processing: false,
        serverSide: false,
        searching: false,
        ajax: {
            url: "{{ route('stock.history.data') }}",
            data: function (d) {
                d.reference = $('#f-reference').val();
                d.transaction_date = $('#f-date').val();
                d.product = $('#f-product').val();
                d.location = $('#f-location').val();
            }
        },
        columns: [
            { data: 'reference' },
            { data: 'transaction_date' },
            { data: 'quantity' },
            { data: 'product', orderable: false },
            { data: 'location', orderable: false }
        ]
    });

    $('#btn-filter').on('click', function () {
        table.ajax.reload();
    });

});

</script>

</body>
</html>
