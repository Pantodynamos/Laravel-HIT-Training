<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.0/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>Transaction History</title>
</head>
<body>

<h2>Transaction History</h2>

<br>

<div style="margin-bottom: 10px;">
    <input type="text" id="f-reference" placeholder="Reference">

    <input type="text" id="f-date" placeholder="Date">

    <input type="text" id="f-product" placeholder="Product">

    <input type="text" id="f-location" placeholder="Location">

    <button type="button" id="btn-filter" class="btn btn-primary">Search</button>
</div>


<table id="history-table" class="display">
    <thead>
        <tr>
            <th>Reference</th>
            <th>Date</th>
            <th class="text-right">Qty</th>
            <th>Product</th>
            <th>Location</th>
        </tr>
    </thead>
</table>

<br>
<a href="{{ route('stock.transaction.form') }}">Transaction</a> |
<a href="{{ route('stock.saldo') }}">Balance Check</a>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/plug-ins/2.0.0/sorting/natural.js"></script>

<script>
$(function () {

let table = $('#history-table').DataTable({
    processing: false,
    serverSide: false,
    searching: false,
    order: [[0, 'asc']],

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
        { 
            data: 'reference', 
            type: 'natural'
        },
 { 
        data: 'transaction_date',
        render: function (data, type, row) {
            if (type === 'sort') {
                // Convert "22-12-2025" to "20251222"
                var parts = data.split('-');
                return parts[2] + parts[1] + parts[0];
            }
            return data;
        }
 },
        {
            data: 'quantity',
            className: 'text-right',
            render: function (data, type) {
                if (type === 'display' || type === 'filter') {
                    return Number(data).toLocaleString('id-ID');
                }
                return data;
            }
        },
        { data: 'product', orderable: false },
        { data: 'location', orderable: false }
    ]
});

$('#btn-filter').on('click', function () {
    table.ajax.reload();
});

});


</script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
$(function () {
    $('#f-date').datepicker({
        dateFormat: 'dd-mm-yy'
    });
});
</script>


</body>
</html>
