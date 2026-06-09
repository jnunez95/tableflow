<?php

namespace App\Http\Controllers;

use App\Models\Table as DiningTable;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function show(DiningTable $table): JsonResponse
    {
        return response()->json([
            'data' => [
                'uuid' => $table->uuid,
                'number' => $table->number,
                'capacity' => $table->capacity,
                'status' => $table->status,
            ],
        ]);
    }
}
