<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table as DiningTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function getBillByTable(DiningTable $table): JsonResponse
    {
        $orders = $this->activeOrdersForTable($table);
        $serviceChargeRate = $this->getActiveServiceChargeRate();
        $taxRate = $this->getActiveTaxRate();

        $aggregatedItems = $this->aggregateBillItems($orders);
        $subtotal = round($aggregatedItems->sum('subtotal'), 2);
        $serviceCharge = round($subtotal * $serviceChargeRate, 2);
        $tax = round(($subtotal + $serviceCharge) * $taxRate, 2);
        $total = round($subtotal + $serviceCharge + $tax, 2);

        return response()->json([
            'data' => [
                'table' => [
                    'uuid' => $table->uuid,
                    'number' => $table->number,
                ],
                'items' => $aggregatedItems->values(),
                'subtotal' => $subtotal,
                'service_charge' => $serviceCharge,
                'service_charge_rate' => $serviceChargeRate,
                'tax' => $tax,
                'tax_rate' => $taxRate,
                'total' => $total,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function closeBillByTable(DiningTable $table): JsonResponse
    {
        $closedCount = DB::transaction(function () use ($table) {
            return Order::query()
                ->where('table_id', $table->id)
                ->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_COMPLETED])
                ->update(['status' => Order::STATUS_COMPLETED]);
        });

        return response()->json([
            'message' => $closedCount > 0
                ? 'Account closed successfully.'
                : 'No active orders to close.',
            'data' => [
                'closed_orders_count' => $closedCount,
            ],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $table = DiningTable::query()->where('uuid', $request->validated('table_uuid'))->firstOrFail();

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
                    'cooking_method' => $this->determineCookingMethod($product),
                ];
            }

            $subtotal = round($subtotal, 2);
            $taxRate = $this->getActiveTaxRate();
            $tax = round($subtotal * $taxRate, 2);
            $total = round($subtotal + $tax, 2);

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

            return $order->load(['items.product:id,name,price', 'table:id,number']);
        });

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => $order,
        ], 201);
    }

    protected function getActiveTaxRate(): float
    {
        return Company::activeTaxRate();
    }

    protected function getActiveServiceChargeRate(): float
    {
        return Company::activeServiceChargeRate();
    }

    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.strtoupper(Str::random(8));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    protected function activeOrdersForTable(DiningTable $table): Collection
    {
        return Order::query()
            ->where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_COMPLETED])
            ->with([
                'items.product.category:id,name,slug',
            ])
            ->orderBy('created_at')
            ->get();
    }

    protected function aggregateBillItems(Collection $orders): Collection
    {
        $items = collect();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    continue;
                }

                $existingKey = $items->search(fn (array $entry) => $entry['product_id'] === $product->id);

                if ($existingKey !== false) {
                    $existing = $items->get($existingKey);
                    $existing['quantity'] += $item->quantity;
                    $existing['subtotal'] = round($existing['subtotal'] + (float) $item->subtotal, 2);
                    $items->put($existingKey, $existing);

                    continue;
                }

                $items->push([
                    'product_id' => $product->id,
                    'category_name' => $product->category?->name ?? 'Other',
                    'category_slug' => $product->category?->slug,
                    'product_name' => $product->name,
                    'description' => $product->description,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]);
            }
        }

        return $items->sortBy('category_name')->values();
    }

    protected function determineCookingMethod(Product $product): ?string
    {
        $category = strtolower($product->category?->name ?? '');
        $name = strtolower($product->name);

        if (str_contains($name, 'salad') || str_contains($category, 'salad') || str_contains($category, 'ensalada')) {
            return 'COLD';
        }

        if (str_contains($name, 'steak') || str_contains($name, 'beef') || str_contains($name, 'wellington') || str_contains($category, 'grill')) {
            return 'GRILL';
        }

        if (str_contains($category, 'soup') || str_contains($name, 'soup') || str_contains($name, 'bisque') || str_contains($name, 'scallop')) {
            return 'HOT';
        }

        if (str_contains($name, 'sauce') || str_contains($name, 'reduction') || str_contains($name, 'wine')) {
            return 'SAUCE';
        }

        return 'PREP';
    }
}
