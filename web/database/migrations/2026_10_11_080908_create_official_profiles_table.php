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
        Schema::connection('mongodb')->create('official_profiles', function (Blueprint $table) {
            $table->uuid('_id');
            $table->string('user_id')->nullable()->unique();
            $table->string('service_number')->unique();
            $table->string('name');
            $table->string('rank');
            $table->unsignedInteger('rank_val');
            $table->string('designation')->nullable();
            $table->string('branch')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('clearance_level')->default(1);
            $table->string('specialization')->nullable();
            $table->dateTime('neo4j_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('official_profiles');
    }
};
