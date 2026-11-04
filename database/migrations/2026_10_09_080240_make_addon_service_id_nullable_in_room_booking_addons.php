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
            Schema::table('room_booking_addons', function (Blueprint $table) {
                $table->unsignedBigInteger('addon_service_id')->nullable()->change();
            });
            return;
        }

        // Try dropping the foreign key safely
        try {
            Schema::table('room_booking_addons', function (Blueprint $table) {
                // Laravel's automatic name might have been 'room_booking_addons_addon_service_id_foreign'
                // If you explicitly named it 'booking_addons_addon_service_id_foreign' before, keep it here.
                $table->dropForeign('booking_addons_addon_service_id_foreign');
            });
        } catch (\Exception $e) {
            // Log it or silently pass if it doesn't exist
        }

        Schema::table('room_booking_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('addon_service_id')->nullable()->change();
            $table->foreign('addon_service_id', 'booking_addons_addon_service_id_foreign')
                ->references('id')
                ->on('room_addon_services')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('room_booking_addons', function (Blueprint $table) {
            $table->dropForeign('booking_addons_addon_service_id_foreign');
        });

        Schema::table('room_booking_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('addon_service_id')->nullable(false)->change();
            $table->foreign('addon_service_id', 'booking_addons_addon_service_id_foreign')
                ->references('id')
                ->on('room_addon_services');
        });
    }
};
