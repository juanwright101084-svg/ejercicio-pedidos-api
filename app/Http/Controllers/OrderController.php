<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return OrderResource::collection(Order::with(['client', 'items.product'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $products = Product::whereIn('id', collect($request->items)->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $total = 0;

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "No hay suficiente stock de \"{$product->name}\". Disponible: {$product->stock}.",
                    ]);
                }

                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'client_id' => $request->client_id,
                'status' => $request->status,
                'total' => $total,
            ]);

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        return new OrderResource($order->load(['client', 'items.product']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return new OrderResource($order->load(['client', 'items.product']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreOrderRequest $request, Order $order)
    {
        $order->update([
            'client_id' => $request->client_id,
            'status' => $request->status,
        ]);

        return new OrderResource($order->load(['client', 'items.product']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->noContent();
    }
}