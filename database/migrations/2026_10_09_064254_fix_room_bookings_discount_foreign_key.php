<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        try {
            Schema::table('room_bookings', function (Blueprint $table) {
                $table->dropForeign('bookings_discount_id_foreign');
            });
        } catch (Throwable) {
            // Foreign key may already have been dropped or renamed
        }

        try {
            Schema::table('room_bookings', function (Blueprint $table) {
                $table->foreign('discount_id')
                    ->references('id')
                    ->on('room_discounts')
                    ->nullOnDelete();
            });
        } catch (Throwable) {
            // Foreign key might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);

            if (Schema::hasTable('discounts')) {
                $table->foreign('discount_id')
                    ->references('id')
                    ->on('discounts')
                    ->nullOnDelete();
            }
        });
    }
};
