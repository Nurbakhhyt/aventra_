<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\PaymentTour;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentTourController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Төлемге бағыттау
     */
    public function pay($bookingId)
    {
        $booking = Booking::with('tour')
            ->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        // PayPal клиент орнату
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $paypal->getAccessToken();

        $amount = $booking->tour->price * $booking->seats;

        $order = $paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => config('paypal.currency', 'USD'),
                    "value" => number_format($amount, 2, '.', '')
                ]
            ]],
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel'),
            ]
        ]);

        if (!isset($order['links']) || !is_array($order['links'])) {
            \Log::error('PayPal createOrder response missing links', ['response' => $order]);
            return redirect()->route('bookingTour.index')->with('error', 'Ошибка при создании платежа в PayPal.');
        }

        // Төлем жазбасы
        PaymentTour::create([
            'user_id' => Auth::id(),
            'booking_id' => $booking->id,
            'status' => 'pending',
            'amount' => $amount,
            'currency' => config('paypal.currency', 'USD'),
            'paypal_response' => json_encode($order),
        ]);

        // PayPal сілтемесіне бағыттау
        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return redirect()->route('bookings.index')->with('error', 'PayPal сілтемесін жасау сәтсіз болды.');
    }

    /**
     * Төлем сәтті болғанда
     */
    public function success(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $paypal->getAccessToken();

        $response = $paypal->capturePaymentOrder($request->token);

        if (!isset($response['status']) || $response['status'] !== 'COMPLETED') {
            return redirect()->route('bookings.index')->with('error', 'Төлем сәтсіз аяқталды.');
        }

        // Төлемді табу немесе ең соңғысын қолдану
        $payment = PaymentTour::where('payment_id', $request->token)->first();
        if (!$payment) {
            $payment = PaymentTour::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        if ($payment) {
            $payment->update([
                'status' => 'approved',
                'payment_id' => $request->token,
                'payer_id' => $response['payer']['payer_id'] ?? null,
                'paypal_response' => $response,
            ]);

            if ($payment->booking) {
                $payment->booking->update([
                    'status' => 'paid',
                    'is_paid' => true,
                ]);
            }
        }

        return redirect()->route('bookings.index')->with('success', 'Оплата сәтті аяқталды!');
    }

    /**
     * Төлемнен бас тарту
     */
    public function cancel()
    {
        return redirect()->route('bookings.index')->with('error', 'Сіз төлемнен бас тарттыңыз.');
    }
}
