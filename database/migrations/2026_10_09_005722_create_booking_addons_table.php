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
        if (Schema::hasTable('room_booking_addons')) {
            return;
        }

        Schema::create('room_booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('room_bookings')->cascadeOnDelete();
            $table->foreignId('addon_service_id')->constrained('room_addon_services')->restrictOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_booking_addons');
    }
};
