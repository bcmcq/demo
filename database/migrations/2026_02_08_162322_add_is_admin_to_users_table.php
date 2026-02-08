<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note: I normally prefer to keep created_at and updated_at at the end of the table,
        // but account_id was already added at the end so I let this go.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('account_id');
        });

        DB::table('users')->where('id', 1)->update(['is_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
