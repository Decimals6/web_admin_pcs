<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $payment = Payment::create(
                $request->only(['total','keterangan','type','customer_id','supplier_id'])
            );

            foreach ($request->details as $detail) {

                $payment->details()->create([
                    'invoice_id' => $detail['invoice_id'],
                    'subtotal' => $detail['subtotal']
                ]);

                $invoice = Invoice::find($detail['invoice_id']);

                $invoice->paid += $detail['subtotal'];

                if ($invoice->paid >= $invoice->grand_total) {
                    $invoice->status = 'paid';
                } elseif ($invoice->paid > 0) {
                    $invoice->status = 'partial';
                }

                $invoice->save();
            }
        });

        return response()->json(['message' => 'Payment success']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
