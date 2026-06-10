<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    public function index(): JsonResponse
    {
        $activeStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PREPARING,
        ];

        $orders = Order::whereIn('status', $activeStatuses)
            ->with([
                'items.product:id,name,image',
                'table:id,number',
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($order) {
                $minutesElapsed = $this->minutesElapsed($order);

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toIso8601String(),
                    'urgency' => $this->urgencyForMinutes($minutesElapsed),
                    'minutes_elapsed' => $minutesElapsed,
                    'table' => [
                        'id' => $order->table->id,
                        'number' => $order->table->number,
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'notes' => $item->notes,
                            'is_ready' => (bool) $item->is_ready,
                            'cooking_method' => $item->cooking_method,
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'image' => $item->product->image,
                            ],
                        ];
                    }),
                ];
            })
            ->sortBy([
                fn (array $order) => match ($order['urgency']) {
                    'urgent' => 1,
                    'medium' => 2,
                    default => 3,
                },
                fn (array $order) => $order['minutes_elapsed'],
            ])
            ->values();

        $activeOrders = $orders->count();
        $delayedOrders = $orders->where('urgency', 'urgent')->count();
        $averageTime = $activeOrders > 0
            ? round($orders->avg('minutes_elapsed'), 1)
            : 0;

        $kitchenStatus = 'optimal';
        if ($delayedOrders > 5) {
            $kitchenStatus = 'critical';
        } elseif ($delayedOrders >= 3) {
            $kitchenStatus = 'busy';
        }

        return response()->json([
            'data' => [
                'stats' => [
                    'active_orders' => $activeOrders,
                    'average_time_minutes' => $averageTime,
                    'delayed_count' => $delayedOrders,
                    'status' => $kitchenStatus,
                ],
                'orders' => $orders,
            ],
        ]);
    }

    protected function minutesElapsed(Order $order): int
    {
        return (int) floor(max(0, $order->created_at->diffInMinutes(now())));
    }

    protected function urgencyForMinutes(int $minutesElapsed): string
    {
        if ($minutesElapsed > 20) {
            return 'urgent';
        }

        if ($minutesElapsed > 10) {
            return 'medium';
        }

        return 'new';
    }

    public function markOrderReady(Order $order): JsonResponse
    {
        DB::transaction(function () use ($order) {
            $order->items()->update(['is_ready' => true]);
            $order->update(['status' => Order::STATUS_SERVED]);
        });

        return response()->json([
            'message' => 'Order marked as ready.',
            'data' => $order->fresh(['items.product', 'table']),
        ]);
    }

    public function toggleItemReady(OrderItem $item): JsonResponse
    {
        if (! in_array($item->order->status, [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PREPARING,
        ], true)) {
            return response()->json([
                'message' => 'This order is no longer active in the kitchen.',
            ], 422);
        }

        $item->update([
            'is_ready' => ! $item->is_ready,
        ]);

        return response()->json([
            'message' => 'Item status updated.',
            'data' => [
                'id' => $item->id,
                'is_ready' => (bool) $item->is_ready,
            ],
        ]);
    }
}
