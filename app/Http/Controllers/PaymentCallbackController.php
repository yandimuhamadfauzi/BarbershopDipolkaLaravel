<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function receive(Request $request)
    {
        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');

        try {
            $notification = new \Midtrans\Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $order_id = $notification->order_id;
        $fraud = $notification->fraud_status;

        Log::info("Midtrans Notification: Order ID $order_id, Status $transaction");

        // Parse order_id (Format: ANTRIAN-{id})
        $order_parts = explode('-', $order_id);
        if (count($order_parts) !== 2 || $order_parts[0] !== 'ANTRIAN') {
            return response()->json(['message' => 'Invalid order_id format'], 400);
        }
        $antrian_id = $order_parts[1];

        $antrian = Antrian::find($antrian_id);

        if (!$antrian) {
            return response()->json(['message' => 'Antrian not found'], 404);
        }

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $antrian->update(['payment_status' => 'pending']);
                } else {
                    $antrian->update(['payment_status' => 'paid']);
                }
            }
        } else if ($transaction == 'settlement') {
            $antrian->update(['payment_status' => 'paid']);
        } else if ($transaction == 'pending') {
            $antrian->update(['payment_status' => 'pending']);
        } else if ($transaction == 'deny') {
            $antrian->update(['payment_status' => 'failed', 'status' => 'Batal']);
        } else if ($transaction == 'expire') {
            $antrian->update(['payment_status' => 'failed', 'status' => 'Batal']);
        } else if ($transaction == 'cancel') {
            $antrian->update(['payment_status' => 'failed', 'status' => 'Batal']);
        }

        return response()->json(['message' => 'Notification processed']);
    }
}
