<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table as DiningTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $table = DiningTable::query()->where('uuid', $request->validated('table_uuid'))->firstOrFail();

        if (! in_array($table->status, [DiningTable::STATUS_AVAILABLE, DiningTable::STATUS_OCCUPIED], true)) {
            throw ValidationException::withMessages([
                'table_uuid' => ['This table is not available for ordering.'],
            ]);
        }

        $items = collect($request->validated('items'));
        $productIds = $items->pluck('product_id')->unique()->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages([
                'items' => ['One or more selected products are unavailable.'],
            ]);
        }

        $order = DB::transaction(function () use ($request, $table, $items, $products) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;
                $lineSubtotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineSubtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $subtotal = round($subtotal, 2);
            $tax = 0;
            $total = $subtotal + $tax;

            $order = Order::query()->create([
                'table_id' => $table->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $request->validated('notes'),
            ]);

            $order->items()->createMany($orderItems);

            if ($table->status === DiningTable::STATUS_AVAILABLE) {
                $table->update(['status' => DiningTable::STATUS_OCCUPIED]);
            }

            return $order->load(['items.product:id,name,price', 'table:id,number']);
        });

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => $order,
        ], 201);
    }

    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.strtoupper(Str::random(8));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
