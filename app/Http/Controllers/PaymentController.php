<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with([
            'customer',
            'details.invoice'
        ]);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $payments = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('nama_customer')->get();

        return view('penjualan.payment.index', compact(
            'payments',
            'customers'
        ));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'customer',
            'details.invoice'
        ]);

        return view('penjualan.payment.show', compact('payment'));
    }
}