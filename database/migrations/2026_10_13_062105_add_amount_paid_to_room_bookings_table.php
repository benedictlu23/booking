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
        Schema::table('room_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('room_bookings', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0)->after('total_price');
            }
        });

        // Backfill existing paid bookings so their amount_paid equals total_price
        DB::table('room_bookings')
            ->where('payment_status', 'paid')
            ->update([
                'amount_paid' => DB::raw('total_price'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('room_bookings', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
        });
    }
};
