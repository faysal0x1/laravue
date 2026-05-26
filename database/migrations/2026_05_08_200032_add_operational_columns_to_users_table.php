<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['admin', 'manager', 'staff', 'customer', 'merchant', 'rider'])->default('customer')->after('password');
            $table->string('avatar')->nullable()->after('type');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'phone', 'type', 'avatar', 'is_active']);
        });
    }
};
