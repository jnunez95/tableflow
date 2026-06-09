<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->string('uuid', 6)->nullable()->after('id');
        });

        $used = [];

        DB::table('tables')->orderBy('id')->lazy()->each(function ($row) use (&$used) {
            do {
                $code = strtoupper(Str::random(6));
            } while (in_array($code, $used, true));

            $used[] = $code;

            DB::table('tables')->where('id', $row->id)->update([
                'uuid' => $code,
            ]);
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
