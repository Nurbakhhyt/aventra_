<?php

namespace App\Http\Controllers;

use App\Models\BookingHotel;
use App\Models\PaymentHotel;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentHotelController extends Controller
{
    private $client;
    private $environment;

    public function __construct()
    {
        $this->environment = new ProductionEnvironment(
            config('services.paypal.client_id'),
            config('services.paypal.secret')
        );

        $this->client = new PayPalHttpClient($this->environment);

        Log::info('PayPal client initialized in production mode');
    }

    public function create(BookingHotel $booking)
    {
        try {
            Log::info('Creating payment for booking', [
                'booking_id' => $booking->id,
                'total_price' => $booking->total_price,
                'amount_in_usd' => round($booking->total_price / 450, 2)
            ]);

            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');

            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => round($booking->total_price / 450, 2)
                    ],
                    'description' => "Бронирование номера {$booking->roomType->name} в отеле {$booking->hotel->name}"
                ]],
                'application_context' => [
                    'cancel_url' => route('bookings.show', $booking),
                    'return_url' => route('payments.success', ['booking' => $booking->id, 'token' => '{token}'])
                ]
            ];

            Log::info('PayPal request', [
                'request_body' => json_encode($request->body)
            ]);

            $response = $this->client->execute($request);

            Log::info('PayPal response', [
                'status' => $response->statusCode,
                'id' => $response->result->id,
                'links' => json_encode($response->result->links)
            ]);

            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    return redirect($link->href);
                }
            }

            Log::error('No approval URL found in PayPal response', [
                'response' => json_encode($response->result)
            ]);

            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Ошибка при создании платежа. Пожалуйста, попробуйте позже.');

        } catch (\Exception $e) {
            Log::error('Payment creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_id' => $booking->id
            ]);

            $errorMessage = 'Ошибка при создании платежа. Пожалуйста, попробуйте позже или используйте другой способ оплаты.';

            if (strpos($e->getMessage(), 'INSTRUMENT_DECLINED') !== false) {
                $errorMessage = 'Ошибка при обработке платежа. Пожалуйста, убедитесь, что ваша карта активна и имеет достаточный баланс. Также проверьте, что карта поддерживает международные платежи.';
            } elseif (strpos($e->getMessage(), 'PAYER_ACTION_REQUIRED') !== false) {
                $errorMessage = 'Требуется дополнительное подтверждение от вашего банка. Пожалуйста, проверьте вашу карту.';
            } elseif (strpos($e->getMessage(), 'PAYMENT_DENIED') !== false) {
                $errorMessage = 'Платеж был отклонен. Пожалуйста, проверьте данные вашей карты.';
            }

            return redirect()->route('bookings.show', $booking)
                ->with('error', $errorMessage);
        }
    }

    public function success(Request $request, BookingHotel $booking)
    {
        try {
            Log::info('Processing payment success', [
                'booking_id' => $booking->id,
                'token' => $request->token,
                'booking_status' => $booking->status
            ]);

            $client = new PayPalHttpClient($this->client->environment);
            $request = new OrdersCaptureRequest($request->token);

            $response = $client->execute($request);

            Log::info('PayPal response', [
                'status' => $response->result->status,
                'id' => $response->result->id,
                'details' => json_encode($response->result)
            ]);

            if ($response->result->status === 'COMPLETED') {
                DB::transaction(function () use ($booking, $response) {
                    $payment = PaymentHotel::create([
                        'booking_id' => $booking->id,
                        'paypal_payment_id' => $response->result->id,
                        'amount' => $booking->total_price,
                        'currency' => 'KZT',
                        'status' => 'paid',
                        'payment_details' => json_encode($response->result)
                    ]);

                    $booking->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid'
                    ]);

                    Log::info('Payment completed successfully', [
                        'booking_id' => $booking->id,
                        'payment_id' => $payment->id
                    ]);
                });

                return redirect()->route('bookings.show', $booking)
                    ->with('success', 'Оплата прошла успешно! Бронирование подтверждено.');
            }

            Log::warning('Payment not completed', [
                'status' => $response->result->status,
                'booking_id' => $booking->id
            ]);

            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Ошибка при обработке платежа. Пожалуйста, свяжитесь с поддержкой.');

        } catch (\Exception $e) {
            Log::error('Payment error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_id' => $booking->id
            ]);

            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Ошибка при обработке платежа. Пожалуйста, свяжитесь с поддержкой.');
        }
    }

    public function cancel(BookingHotel $booking)
    {
        Log::info('Payment cancelled for booking: ' . $booking->id);
        return redirect()
            ->route('bookings.show', $booking)
            ->with('error', 'Оплата отменена');
    }
}
