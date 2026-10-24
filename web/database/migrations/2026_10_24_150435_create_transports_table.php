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
        Schema::create('transports', function (Blueprint $collection) {
            $collection->string('vehicle_no')->primary();
            $collection->string('name');
            $collection->enum('vehicle_type', [
                'motorcycle',
                'car',
                'pickup',
                'truck',
                'armored_vehicle',
                'ambulance',
                'boat',
                'helicopter',
                'aircraft',
            ]);
            $collection->string('kind');
            $collection->string('manufacturer')->nullable();
            $collection->string('model')->nullable();
            $collection->unsignedSmallInteger('manufacture_year')->nullable();
            $collection->decimal('speed_kmh', 6, 2);
            $collection->enum('fuel_type', [
                'petrol',
                'diesel',
                'electric',
                'hybrid',
                'aviation_fuel',
                'marine_diesel',
            ]);
            $collection->unsignedSmallInteger('crew_capacity')->default(1);
            $collection->unsignedSmallInteger('passenger_capacity')->default(0);
            $collection->unsignedInteger('cargo_capacity_kg')->default(0);
            $collection->decimal('range_km', 8, 2)->nullable();
            $collection->enum('status', [
                'available',
                'deployed',
                'maintenance',
                'retired',
            ])->default('available');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};
