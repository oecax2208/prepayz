<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Transaction;

// PAYMENT MENGGUNAKAN FASPAY
class PaymentController extends Controller
{
    public function redirectToFaspay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => [
                'required',
                Rule::unique('orders', 'order_id')
            ],
            'amount' => 'required|numeric',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $payload = [
            'request' => 'Post Data Transaction',
            'merchant_id' => config('services.faspay.merchant_id'),
            'merchant' => config('services.faspay.merchant_name'),
            'bill_no' => $request->order_id,
            'bill_reff' => $request->order_id,
            'bill_date' => date('Y-m-d H:i:s'),
            'bill_expired' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'bill_desc' => $request->description,
            'bill_currency' => 'IDR',
            'bill_gross' => $request->amount,
            'bill_miscfee' => 0,
            'bill_total' => $request->amount,
            'cust_no' => '0000000000000000',
            'cust_name' => 'John Doe',
            'payment_channel' => 15,
            'bank_userid' => '',
            'msisdn' => '',
            'email' => 'john.doe@example.com',
            'terminal' => '10',
            'billing_address' => 'Jl. Bypass Kuta No.101',
            'billing_address_city' => 'Kuta',
            'billing_address_region' => 'Bali',
            'billing_address_state' => 'ID',
            'billing_address_poscode' => '80361',
            'billing_mall_name' => '',
            'billing_telkom' => '',
            'billing_email' => 'john.doe@example.com',
            'billing_sent_email' => '1',
            'response_type' => '3',
            'return_url' => config('services.faspay.redirect_url'),
            'timeout' => '',
            'signature' => '',
        ];

        // Generate signature
        $signature = md5(config('services.faspay.user_id').config('services.faspay.password').$payload['merchant_id'].$payload['bill_no'].$payload['bill_reff'].$payload['bill_total'].$payload['bill_currency']);
        $payload['signature'] = $signature;

        // Redirect
        $redirectUrl = 'https://fpgdev.faspay.co.id/payment';
        $queryString = http_build_query($payload);
        $redirectUrl .= '?'.$queryString;
        return Redirect::away($redirectUrl);
    }

    public function handleFaspayCallback(Request $request)
    {
        // Validate response signature
        $signature = md5($request->response.$request->trx_id.config('services.faspay.merchant_id').config('services.faspay.merchant_name').$request->bill_no.$request->payment_reff.$request->payment_date.$request->payment_status.$request->payment_total.$request->payment_channel.config('services.faspay.user_id').config('services.faspay.password'));
        if ($signature !== $request->signature) {
            return response('Invalid signature', 400);
        }

        // Handle payment status
        $orderId = $request->bill_no;
        $paymentStatus = $request->payment_status;

        // Save transaction to database
        $transaction = new Transaction([
            'transaction_id' => $request->trx_id,
            'order_id' => $orderId,
            'amount' => $request->payment_total,
            'payment_status' => $paymentStatus,
            // Add other attributes here
        ]);
        $transaction->save();

        if ($paymentStatus == '2') { // Payment success
            // Update order status and do other necessary actions
            // ...
            return view('payment.success');
        } else if ($paymentStatus == '3') { // Payment failed
            // Update order status and do other necessary actions
            // ...
            return view('payment.failed');
        } else { // Payment pending
            // Update order status and do other necessary actions
            // ...
            return view('payment.pending');
        }
    }
}
