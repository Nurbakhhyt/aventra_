<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\PaymentTour;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Auth;

class PaymentTourController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Перенаправление на PayPal
    public function pay($bookingId)
    {
        $booking = Booking::with('tour')->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending') // можно менять под свои статусы
            ->firstOrFail();

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        $amount = $booking->tour->price * $booking->seats; // расчёт полной суммы

        $order = $paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "USD",
                    "value" => number_format($amount, 2, '.', '')
                ]
            ]],
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel'),
            ]
        ]);

        // Сохраняем черновик платежа
        PaymentTour::create([
            'user_id' => Auth::id(),
            'booking_id' => $booking->id,
            'status' => 'pending',
            'amount' => $amount,
            'currency' => 'USD',
            'paypal_response' => $order,
        ]);

        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return redirect()->route('bookings.index')->with('error', 'Ошибка при создании оплаты.');
    }

    // Успешная оплата
    public function success(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        $response = $paypal->capturePaymentOrder($request->token);

        if ($response['status'] === 'COMPLETED') {
            $payment = PaymentTour::where('payment_id', $request->token)->first();

            if (!$payment) {
                // Поиск последней неподтверждённой записи
                $payment = PaymentTour::where('user_id', Auth::id())
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }

            $payment->update([
                'status' => 'approved',
                'payment_id' => $request->token,
                'payer_id' => $response['payer']['payer_id'] ?? null,
                'paypal_response' => $response,
            ]);

            $payment->booking->update([
                'status' => 'paid',
            ]);

            return redirect()->route('bookings.index')->with('success', 'Оплата прошла успешно!');
        }

        return redirect()->route('bookings.index')->with('error', 'Ошибка оплаты.');
    }

    // Отмена оплаты
    public function cancel()
    {
        return redirect()->route('bookings.index')->with('error', 'Вы отменили оплату.');
    }
}
