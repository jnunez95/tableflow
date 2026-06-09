<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@tableflow.test'],
            [
                'name' => 'TableFlow Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $tenant = Tenant::query()->firstOrCreate(
            ['id' => 'demo'],
            ['name' => 'LUMIÈRE DINING'],
        );

        if (! $tenant->domains()->where('domain', 'demo')->exists()) {
            $tenant->domains()->create([
                'domain' => 'demo',
            ]);
        }

        $tenant->run(function (): void {
            User::query()->firstOrCreate(
                ['email' => 'admin@tableflow.test'],
                [
                    'name' => 'TableFlow Admin',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
        });
    }
}
