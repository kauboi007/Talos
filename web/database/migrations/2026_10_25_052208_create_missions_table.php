<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('troop_name');
            $table->foreignIdFor(User::class, 'created_by');
            $table->foreignIdFor(User::class, 'approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('approved');
            $table->boolean('feasible')->default(false);
            $table->json('planning_result');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
