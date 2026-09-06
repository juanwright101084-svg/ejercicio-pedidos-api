<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    #[OA\Post(
        path: "/payments",
        summary: "Procesar el pago de una orden mediante Stripe",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_id"],
                properties: [
                    new OA\Property(property: "order_id", type: "integer", example: 1),
                    new OA\Property(property: "payment_method", type: "string", example: "pm_card_visa", description: "Token de tarjeta de prueba de Stripe. Si se omite, se usa pm_card_visa por defecto."),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Pago procesado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Payment")
            ),
            new OA\Response(response: 402, description: "El pago fue rechazado por Stripe"),
            new OA\Response(response: 404, description: "Orden no encontrada"),
            new OA\Response(response: 422, description: "Error de validación"),
        ]
    )]
    public function store(StorePaymentRequest $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $order = Order::findOrFail($request->order_id);

        try {
            $intent = PaymentIntent::create([
                'amount' => (int) round($order->total * 100),
                'currency' => 'usd',
                'payment_method' => $request->payment_method ?? 'pm_card_visa',
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'description' => "Pago de la orden #{$order->id}",
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::guard('api')->id(),
                'amount' => $order->total,
                'currency' => 'usd',
                'stripe_payment_intent_id' => $intent->id,
                'status' => $intent->status,
            ]);

            return response()->json($payment, 201);

        } catch (CardException $e) {
            Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::guard('api')->id(),
                'amount' => $order->total,
                'currency' => 'usd',
                'status' => 'failed',
            ]);

            return response()->json([
                'message' => 'El pago fue rechazado.',
                'error' => $e->getError()->message,
            ], 402);
        }
    }

    #[OA\Get(
        path: "/my-orders",
        summary: "Historial de compras del usuario autenticado",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de pagos/compras del usuario",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Payment")
                )
            ),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function myOrders()
    {
        $payments = Payment::where('user_id', Auth::guard('api')->id())
            ->with('order.items.product')
            ->latest()
            ->get();

        return response()->json($payments);
    }
}