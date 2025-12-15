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
        // validate transaction type first
        $request->validate([
            'type' => 'required|in:IN,OUT',
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        // normalize date input (dd-mm-yyyy → yyyy-mm-dd)
        $normalizedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)
        ->format('Y-m-d');

        if ($request->type === 'IN') {
            $request->merge(['date_in' => $normalizedDate]);

        return $this->addStock($request);
    }

        // OUT
        $request->merge(['transaction_date' => $normalizedDate]);

        return $this->reduceStock($request);
    }

    // Generate Reference
    private function generateReference($program)
    {
        $prog = Program::where('program', $program)->lockForUpdate()->first();
        if (!$prog) {
            abort(500, "Program $program not found in table programs");
        }

        $prog->increment('counter');

        return $program . $prog->counter;
    }

    // Tambah Stok  
    public function addStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'date_in' => 'required|date',
    ]);
        DB::beginTransaction();
        try {
            
            // Validasi tanggal
            $last = Stock::where('product_id', $request->product_id)
                ->where('location_id', $request->location_id)
                ->orderBy('date_in', 'desc')
                ->first();

            if ($last && $request->date_in < $last->date_in) {
                DB::rollBack();
                return back()->with('error', 'Tanggal masuk tidak valid');
            }

            // Gabungkan batch jika ada tanggal yang sama
            $batch = Stock::where('product_id', $request->product_id)
                ->where('location_id', $request->location_id)
                ->where('date_in', $request->date_in)
                ->first();

            if ($batch) {
                $batch->quantity += $request->quantity;
                $batch->save();
            } else {
                $batch = Stock::create([
                    'product_id' => $request->product_id,
                    'location_id' => $request->location_id,
                    'quantity' => $request->quantity,
                    'date_in' => $request->date_in
                ]);
            }

            $reference = $this->generateReference("TAMBAH");

            StockTransaction::create([
                'reference' => $reference,
                'transaction_date' => $request->date_in,
                'quantity' => $request->quantity,
                'stock_id' => $batch->id,
                'program_id' => 'TAMBAH'
            ]);

            DB::commit();
            return back()->with('success', 'Stok berhasil ditambahkan! Ref: '.$reference);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // Kurangi Stok FIFO
    public function reduceStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
    ]);
        DB::beginTransaction();
        try {
            $qty = $request->quantity;

            // Cek saldo mencukupi atau tidak
            $total = Stock::where('product_id', $request->product_id)
                ->where('location_id', $request->location_id)
                ->sum('quantity');

            if ($total < $qty) {
                DB::rollBack();
                return back()->with('error', 'Saldo tidak mencukupi');
            }

            $last = Stock::where('product_id', $request->product_id)
                ->where('location_id', $request->location_id)
                ->orderBy('date_in', 'desc')
                ->first();

            if ($last && $request->transaction_date < $last->date_in) {
                DB::rollBack();
                return back()->with('error', 'Tanggal transaksi tidak valid');
            }

            $reference = $this->generateReference("KURANG");

            $batches = Stock::where('product_id', $request->product_id)
                ->where('location_id', $request->location_id)
                ->where('quantity', '>', 0)
                ->orderBy('date_in')
                ->get();

            foreach ($batches as $batch) {
                if ($qty <= 0) break;

                $use = min($batch->quantity, $qty);
                $batch->quantity -= $use;
                $batch->save();

                StockTransaction::create([
                    'reference' => $reference,
                    'transaction_date' => $request->transaction_date,
                    'quantity' => $use,
                    'stock_id' => $batch->id,
                    'program_id' => 'KURANG'
                ]);

                $qty -= $use;
            }

            DB::commit();
            return back()->with('success', 'Stok berhasil dikurangi! Ref: '.$reference);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // Report Saldo
    public function saldo(Request $request)
{
    $products = Product::all();
    $locations = Location::all();

    $stock = null;
    $saldo = null;

    if ($request->filled(['product_id', 'location_id'])) {

        $stock = Stock::with(['product', 'location'])
            ->where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->first();

        if ($stock) {
            $saldo = $stock->transactions()->sum('quantity');
        } else {
            $saldo = 0;
        }
    }

    return view('stock.report_saldo', compact(
        'products',
        'locations',
        'stock',
        'saldo'
    ));
}


    // Report History
public function history(Request $request)
{
    $request->validate([
        'reference' => 'nullable|string',
        'transaction_date' => 'nullable|date_format:d-m-Y',
        'product' => 'nullable|string',
        'location' => 'nullable|string',
    ]);

    $products = Product::all();
    $locations = Location::all();

    $date = $request->transaction_date
    ? Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d')
    : null;

    $report = StockTransaction::with(['stock.product', 'stock.location'])

        ->when($request->reference, function ($q) use ($request) {
            $q->where('reference', 'like', '%' . $request->reference . '%');
        })

        ->when($date, function ($q) use ($date) {
            $q->whereDate('transaction_date', $date);
        })

        ->when($request->product, function ($q) use ($request) {
            $q->whereHas('stock.product', function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->product . '%');
            });
        })

        ->when($request->location, function ($q) use ($request) {
            $q->whereHas('stock.location', function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->location . '%');
            });
        })

        ->orderBy('created_at', 'desc')
        ->paginate(5)
        ->appends($request->query());

    return view('stock.report_history', compact('report', 'products', 'locations'));
}


}
