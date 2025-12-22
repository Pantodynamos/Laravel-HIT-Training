<?php

namespace App\Http\Controllers;

use App\Product;
use App\Location;
use App\Stock;
use App\StockTransaction;
use App\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class StockController extends Controller
{
    public function transactionForm()
    {
            return view('stock.transaction', [
                'products' => Product::all(),
                'locations' => Location::all(),
            ]);
    }   
    
    public function handleTransaction(Request $request)
{
    try {
        $validated = $request->validate([
            'type'             => 'required|in:IN,OUT',
            'product_id'       => 'required|exists:products,id',
            'location_id'      => 'required|exists:locations,id',
            'quantity'         => 'required|integer|min:1',
            'transaction_date' => 'required|date',
        ]);

        // normalize date input (dd-mm-yyyy -> yyyy-mm-dd)
        $normalizedDate = Carbon::createFromFormat(
            'd-m-Y',
            $validated['transaction_date']
        )->format('Y-m-d');

        if ($validated['type'] === 'IN') {
            $request->merge([
                'date_in' => $normalizedDate
            ]);

            $result = $this->addStock($request);
        } else {
            // OUT
            $request->merge([
                'transaction_date' => $normalizedDate
            ]);

            $result = $this->reduceStock($request);
        }

        return response()->json([
            'message' => 'Transaction saved successfully',
            'reference' => $result['reference']
        ]);

    } catch (Throwable $e) {
        report($e);

        return response()->json([
            'message' => 'Transaction failed, please try again'
        ], 500);
    }
}

    // Generate Reference
    private function generateReference(string $programCode): array
    {
    $program = Program::where('program', $programCode)
        ->lockForUpdate()
        ->first();

    if (!$program) {
        abort(500, "Program {$programCode} not found");
    }

    $program->increment('counter');

    return [
        'reference'   => $programCode . $program->counter,
        'program_id'  => $program->id,
    ];
}



    // Tambah Stok  
public function addStock(Request $request): array
{
    DB::beginTransaction();

    try {
        $last = Stock::where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->orderBy('date_in', 'desc')
            ->first();

        if ($last && $request->date_in < $last->date_in) {
            throw new \Exception('Invalid stock date');
        }

        $batch = Stock::where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->where('date_in', $request->date_in)
            ->first();

        if ($batch) {
            $batch->quantity += $request->quantity;
            $batch->save();
        } else {
            $batch = Stock::create([
                'product_id'  => $request->product_id,
                'location_id' => $request->location_id,
                'quantity'    => $request->quantity,
                'date_in'     => $request->date_in,
            ]);
        }

        $ref = $this->generateReference('TAMBAH');

        StockTransaction::create([
            'reference'        => $ref['reference'],
            'transaction_date' => $request->date_in,
            'quantity'         => $request->quantity,
            'stock_id'         => $batch->id,
            'program_id'       => $ref['program_id'],
        ]);

        DB::commit();

        return [
            'reference' => $ref['reference']
        ];

    } catch (Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}

    // Kurangi Stok secara FIFO
public function reduceStock(Request $request): array
{
    DB::beginTransaction();

    try {
        $qty = $request->quantity;

        $total = Stock::where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->sum('quantity');

        if ($total < $qty) {
            throw new \Exception('Insufficient stock');
        }

        $last = Stock::where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->orderBy('date_in', 'desc')
            ->first();

        if ($last && $request->transaction_date < $last->date_in) {
            throw new \Exception('Invalid transaction date');
        }

        $ref = $this->generateReference('KURANG');

        $batches = Stock::where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->where('quantity', '>', 0)
            ->orderBy('date_in')
            ->get();

        foreach ($batches as $batch) {
            if ($qty <= 0) {
                break;
            }

            $use = min($batch->quantity, $qty);

            $batch->quantity -= $use;
            $batch->save();

            StockTransaction::create([
                'reference'        => $ref['reference'],
                'transaction_date' => $request->transaction_date,
                'quantity'         => -$use,
                'stock_id'         => $batch->id,
                'program_id'       => $ref['program_id'],
            ]);

            $qty -= $use;
        }

        DB::commit();

        return [
            'reference' => $ref['reference']
        ];

    } catch (Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}


    // Report Saldo
public function saldoData(Request $request)
{
    try {
        if (!$request->filled(['product_id', 'location_id'])) {
            return response()->json([
                'stock' => [],
                'saldo' => 0,
            ]);
        }

        $stock = Stock::with(['product', 'location'])
            ->where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->get();

        return response()->json([
            'stock' => $stock,
            'saldo' => $stock->sum('quantity'),
        ]);

    }catch (Throwable $e) {
    report($e);

    return response()->json([
        'message' => 'An error occurred, please try again later.'], 500);
}
}

public function saldo()
{
    return view('stock.report_saldo', [
        'products'  => Product::all(),
        'locations' => Location::all(),
    ]);
}



    // Report History
public function history()
{
    // render page only for ajax
    return view('stock.report_history');
}

public function historyData(Request $request)
{
    $query = StockTransaction::with(['stock.product', 'stock.location']);
    
    if ($request->filled('reference')) {
        $query->where('reference', 'like', $request->reference . '%');
    }

    if ($request->filled('transaction_date')) {
        $date = Carbon::createFromFormat('d-m-Y', $request->transaction_date)
            ->format('Y-m-d');
        $query->whereDate('transaction_date', $date);
    }

    if ($request->filled('product')) {
        $query->whereHas('stock.product', function ($q) use ($request) {
            $q->where('name', 'like', $request->product . '%');
        });
    }

    if ($request->filled('location')) {
        $query->whereHas('stock.location', function ($q) use ($request) {
            $q->where('name', 'like', $request->location . '%');
        });
    }

    // fetch dulu baru di group
    $rows = $query->orderBy('transaction_date', 'desc')->get();

    $grouped = $rows->groupBy(function ($row) {
        return implode('|', [
            $row->reference,
            optional($row->stock)->product_id,
            optional($row->stock)->location_id,
            $row->transaction_date,
        ]);
    });

    $final = $grouped->map(function ($items) {
        $first = $items->first();

        return [
            'reference' => $first->reference,
            'transaction_date' => Carbon::parse($first->transaction_date)->format('d-m-Y'),
            'quantity' => $items->sum('quantity'),
            'product' => optional($first->stock->product)->name,
            'location' => optional($first->stock->location)->name,
        ];
    })->values();

    return datatables()->of($final)->make(true);
}

}
