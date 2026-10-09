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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('room_categories')->cascadeOnDelete();
            $table->string('room_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('capacity_adults')->default(2);
            $table->unsignedSmallInteger('capacity_children')->default(0);
            $table->boolean('allows_pets')->default(false);
            $table->decimal('base_price', 10, 2);
            $table->decimal('pet_fee', 10, 2)->default(0);
            $table->unsignedSmallInteger('floor_number')->nullable();
            // status: available, occupied, maintenance, housekeeping, blocked
            $table->string('status')->default('available');
            // type: overnight, day_pass, both
            $table->string('booking_type')->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
