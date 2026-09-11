<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('role');
        });

        \Illuminate\Support\Facades\DB::table('users')->update(['status' => 'approved']);

        \Illuminate\Support\Facades\DB::table('users')
            ->where('email', 'wahyusnjy@gmail.com')
            ->update([
                'role' => 'admin',
                'status' => 'approved',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
