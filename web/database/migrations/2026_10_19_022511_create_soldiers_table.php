<?php

use App\Models\User;
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
        Schema::create('soldiers', function (Blueprint $collection) {
            $collection->string('service_id')->primary();
            $collection->foreignIdFor(User::class)->unique();
            $collection->date('dob');
            $collection->enum('bloodGroup', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $collection->string('street');
            $collection->string('city');
            $collection->string('state');
            $collection->string('pincode');
            $collection->string('rank');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soldiers');
    }
};
