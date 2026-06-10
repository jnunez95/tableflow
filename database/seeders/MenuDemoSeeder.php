<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table as DiningTable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tables = collect([
            ['number' => 1],
            ['number' => 2],
            ['number' => 3],
            ['number' => 4],
            ['number' => 5],
            ['number' => 6],
        ])->map(fn (array $data) => DiningTable::query()->firstOrCreate(
            ['number' => $data['number']],
            $data,
        ));

        $categories = [
            [
                'name' => 'Entradas',
                'description' => 'Aperturas refinadas para despertar el paladar.',
                'sort_order' => 1,
                'products' => [
                    [
                        'name' => 'Tartare de Atún',
                        'description' => 'Atún aleta amarilla, aguacate, tobiko y emulsión cítrica.',
                        'price' => 18.50,
                        'dietary_tags' => ['Gluten-Free'],
                        'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Burrata de Temporada',
                        'description' => 'Burrata cremosa, tomates confitados, pesto de albahaca.',
                        'price' => 16.00,
                        'dietary_tags' => ['Vegetarian'],
                        'image' => 'https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Carpaccio de Res',
                        'description' => 'Lomo fino, rúcula, parmesano y aceite de trufa.',
                        'price' => 19.00,
                        'dietary_tags' => ['Gluten-Free'],
                        'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Platos Fuertes',
                'description' => 'Creaciones de autor con técnica y equilibrio.',
                'sort_order' => 2,
                'products' => [
                    [
                        'name' => 'Salmón Glaseado',
                        'description' => 'Glaseado de miso, puré de coliflor y eneldo fresco.',
                        'price' => 32.00,
                        'dietary_tags' => ['Gluten-Free', 'Chef\'s Choice'],
                        'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Risotto de Hongos',
                        'description' => 'Arroz carnaroli, mix de hongos silvestres y parmesano.',
                        'price' => 26.00,
                        'dietary_tags' => ['Vegetarian'],
                        'image' => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Filete Angus',
                        'description' => 'Corte de 250g, mantequilla de hierbas y papas fondant.',
                        'price' => 42.00,
                        'dietary_tags' => ['Chef\'s Choice'],
                        'image' => 'https://images.unsplash.com/photo-1600891964092-4316c288032e?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Pasta al Tartufo',
                        'description' => 'Tagliatelle fresca, crema ligera y laminas de trufa negra.',
                        'price' => 34.00,
                        'dietary_tags' => ['Vegetarian'],
                        'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?auto=format&fit=crop&w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Postres',
                'description' => 'Finales dulces con un toque contemporáneo.',
                'sort_order' => 3,
                'products' => [
                    [
                        'name' => 'Fondant de Chocolate',
                        'description' => 'Centro líquido, helado de vainilla bourbon.',
                        'price' => 14.00,
                        'dietary_tags' => ['Vegetarian'],
                        'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Tarta de Limón',
                        'description' => 'Merengue suizo, curd de limón y base de almendra.',
                        'price' => 12.00,
                        'dietary_tags' => ['Gluten-Free'],
                        'image' => 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?auto=format&fit=crop&w=800&q=80',
                    ],
                ],
            ],
            [
                'name' => 'Bebidas',
                'description' => 'Selección de cócteles y bebidas sin alcohol.',
                'sort_order' => 4,
                'products' => [
                    [
                        'name' => 'Negroni Clásico',
                        'description' => 'Gin, vermut rosso y bitter de naranja.',
                        'price' => 15.00,
                        'dietary_tags' => ['Vegan'],
                        'image' => 'https://images.unsplash.com/photo-1536935338788-846bb9981813?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'name' => 'Agua Mineral',
                        'description' => 'Agua mineral natural, 750ml.',
                        'price' => 5.00,
                        'dietary_tags' => ['Vegan', 'Gluten-Free'],
                        'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=800&q=80',
                    ],
                ],
            ],
        ];

        $productsBySlug = [];

        foreach ($categories as $categoryData) {
            $products = $categoryData['products'];
            unset($categoryData['products']);

            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    ...$categoryData,
                    'slug' => Str::slug($categoryData['name']),
                    'is_active' => true,
                ],
            );

            foreach ($products as $index => $productData) {
                $product = Product::query()->updateOrCreate(
                    ['slug' => Str::slug($productData['name'])],
                    [
                        ...$productData,
                        'slug' => Str::slug($productData['name']),
                        'category_id' => $category->id,
                        'sort_order' => $index + 1,
                        'is_available' => true,
                    ],
                );

                $productsBySlug[$product->slug] = $product;
            }
        }

        $this->seedOrder(
            table: $tables->firstWhere('number', 1),
            orderNumber: 'ORD-DEMO001',
            status: Order::STATUS_PENDING,
            notes: 'Sin cebolla en el tartare.',
            items: [
                ['slug' => 'tartare-de-atun', 'quantity' => 2],
                ['slug' => 'negroni-clasico', 'quantity' => 2],
            ],
        );

        $this->seedOrder(
            table: $tables->firstWhere('number', 3),
            orderNumber: 'ORD-DEMO002',
            status: Order::STATUS_PREPARING,
            notes: null,
            items: [
                ['slug' => 'salmon-glaseado', 'quantity' => 1],
                ['slug' => 'risotto-de-hongos', 'quantity' => 1],
                ['slug' => 'fondant-de-chocolate', 'quantity' => 2],
            ],
        );

        $this->seedOrder(
            table: $tables->firstWhere('number', 6),
            orderNumber: 'ORD-DEMO003',
            status: Order::STATUS_SERVED,
            notes: 'Celebración de aniversario.',
            items: [
                ['slug' => 'filete-angus', 'quantity' => 2],
                ['slug' => 'pasta-al-tartufo', 'quantity' => 1],
                ['slug' => 'tarta-de-limon', 'quantity' => 2],
                ['slug' => 'agua-mineral', 'quantity' => 3],
            ],
        );

        $this->seedOrder(
            table: $tables->firstWhere('number', 1),
            orderNumber: 'ORD-DEMO004',
            status: Order::STATUS_COMPLETED,
            notes: null,
            items: [
                ['slug' => 'burrata-de-temporada', 'quantity' => 1],
                ['slug' => 'carpaccio-de-res', 'quantity' => 1],
            ],
            createdAt: now()->subHours(2),
        );
    }

    /**
     * @param  array<int, array{slug: string, quantity: int}>  $items
     */
    protected function seedOrder(
        DiningTable $table,
        string $orderNumber,
        string $status,
        ?string $notes,
        array $items,
        ?\DateTimeInterface $createdAt = null,
    ): void {
        if (Order::query()->where('order_number', $orderNumber)->exists()) {
            return;
        }

        $subtotal = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $product = Product::query()->where('slug', $item['slug'])->firstOrFail();
            $quantity = $item['quantity'];
            $unitPrice = (float) $product->price;
            $lineSubtotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineSubtotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
            ];
        }

        $subtotal = round($subtotal, 2);

        $order = Order::query()->create([
            'table_id' => $table->id,
            'order_number' => $orderNumber,
            'status' => $status,
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal,
            'notes' => $notes,
            'created_at' => $createdAt ?? now(),
            'updated_at' => $createdAt ?? now(),
        ]);

        $order->items()->createMany($orderItems);
    }
}
