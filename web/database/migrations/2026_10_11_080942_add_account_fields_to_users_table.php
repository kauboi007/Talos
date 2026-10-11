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
        Schema::connection('mongodb')->table('users', function (Blueprint $table) {
            $table->string('service_id')->nullable()->unique();
            $table->string('role')->default('commander');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->table('users', function (Blueprint $table) {
            $table->dropColumn(['service_id', 'role', 'is_active', 'last_login_at']);
        });
    }
};
