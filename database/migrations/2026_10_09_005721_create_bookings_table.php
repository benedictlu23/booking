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
        if (Schema::hasTable('bookings')) {
            return;
        }

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete();

            // Guest info (for walk-ins or guests without accounts)
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();

            // Stay details
            $table->dateTime('check_in');
            $table->dateTime('check_out');
            // booking_type: overnight, day_pass
            $table->string('booking_type')->default('overnight');

            // Guest breakdown
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('pets')->default(0);

            // Pricing
            $table->decimal('base_price', 10, 2);
            $table->decimal('addons_total', 10, 2)->default(0);
            $table->decimal('pet_fee_total', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);

            // Statuses
            // status: pending, confirmed, checked_in, checked_out, cancelled, no_show
            $table->string('status')->default('pending');
            // payment_status: unpaid, partial, paid, refunded
            $table->string('payment_status')->default('unpaid');

            $table->boolean('is_walk_in')->default(false);
            $table->text('special_requests')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
