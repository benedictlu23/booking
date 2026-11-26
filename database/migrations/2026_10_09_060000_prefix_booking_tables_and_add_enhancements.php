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
        // 1. Rename pivot table first before renaming entity table 'amenities' -> 'room_amenities'
        if (Schema::hasTable('room_amenities') && ! Schema::hasTable('room_room_amenities')) {
            Schema::rename('room_amenities', 'room_room_amenities');
        }

        if (Schema::hasTable('amenities') && ! Schema::hasTable('room_amenities')) {
            Schema::rename('amenities', 'room_amenities');
        }

        if (Schema::hasTable('bookings') && ! Schema::hasTable('room_bookings')) {
            Schema::rename('bookings', 'room_bookings');
        }

        if (Schema::hasTable('booking_discounts') && ! Schema::hasTable('room_discounts')) {
            Schema::rename('booking_discounts', 'room_discounts');
        } elseif (Schema::hasTable('room_booking_discounts') && ! Schema::hasTable('room_discounts')) {
            Schema::rename('room_booking_discounts', 'room_discounts');
        } elseif (Schema::hasTable('discounts') && ! Schema::hasTable('room_discounts')) {
            Schema::rename('discounts', 'room_discounts');
        }

        if (Schema::hasTable('booking_addons') && ! Schema::hasTable('room_booking_addons')) {
            Schema::rename('booking_addons', 'room_booking_addons');
        }

        if (Schema::hasTable('addon_services') && ! Schema::hasTable('room_addon_services')) {
            Schema::rename('addon_services', 'room_addon_services');
        }

        if (Schema::hasTable('housekeeping_logs') && ! Schema::hasTable('room_housekeeping_logs')) {
            Schema::rename('housekeeping_logs', 'room_housekeeping_logs');
        }

        if (Schema::hasTable('resort_settings') && ! Schema::hasTable('room_resort_settings')) {
            Schema::rename('resort_settings', 'room_resort_settings');
        }

        if (Schema::hasTable('carousels') && ! Schema::hasTable('room_carousels')) {
            Schema::rename('carousels', 'room_carousels');
        }

        // Add enhancement columns to room_bookings
        Schema::table('room_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('room_bookings', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('room_bookings', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_method');
            }
        });

        // Add enhancement columns to rooms
        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'gallery')) {
                $table->json('gallery')->nullable()->after('description');
            }
        });

        // Add enhancement columns to room_housekeeping_logs
        Schema::table('room_housekeeping_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('room_housekeeping_logs', 'staff_name')) {
                $table->string('staff_name')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('room_housekeeping_logs', 'cleaning_timestamp')) {
                $table->timestamp('cleaning_timestamp')->nullable()->after('completed_at');
            }
        });

        // Add enhancement columns to room_booking_addons
        Schema::table('room_booking_addons', function (Blueprint $table) {
            if (! Schema::hasColumn('room_booking_addons', 'item_name')) {
                $table->string('item_name')->nullable()->after('addon_service_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('room_booking_addons') && Schema::hasColumn('room_booking_addons', 'item_name')) {
            Schema::table('room_booking_addons', function (Blueprint $table) {
                $table->dropColumn('item_name');
            });
        }

        if (Schema::hasTable('room_housekeeping_logs')) {
            Schema::table('room_housekeeping_logs', function (Blueprint $table) {
                if (Schema::hasColumn('room_housekeeping_logs', 'staff_name')) {
                    $table->dropColumn('staff_name');
                }
                if (Schema::hasColumn('room_housekeeping_logs', 'cleaning_timestamp')) {
                    $table->dropColumn('cleaning_timestamp');
                }
            });
        }

        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'gallery')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('gallery');
            });
        }

        if (Schema::hasTable('room_bookings')) {
            Schema::table('room_bookings', function (Blueprint $table) {
                if (Schema::hasColumn('room_bookings', 'payment_proof')) {
                    $table->dropColumn('payment_proof');
                }
                if (Schema::hasColumn('room_bookings', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
            });
        }

        if (Schema::hasTable('room_carousels')) {
            Schema::rename('room_carousels', 'carousels');
        }
        if (Schema::hasTable('room_resort_settings')) {
            Schema::rename('room_resort_settings', 'resort_settings');
        }
        if (Schema::hasTable('room_housekeeping_logs')) {
            Schema::rename('room_housekeeping_logs', 'housekeeping_logs');
        }
        if (Schema::hasTable('room_addon_services')) {
            Schema::rename('room_addon_services', 'addon_services');
        }
        if (Schema::hasTable('room_booking_addons')) {
            Schema::rename('room_booking_addons', 'booking_addons');
        }
        if (Schema::hasTable('room_discounts')) {
            Schema::rename('room_discounts', 'booking_discounts');
        }
        if (Schema::hasTable('room_bookings')) {
            Schema::rename('room_bookings', 'bookings');
        }
        if (Schema::hasTable('room_amenities')) {
            Schema::rename('room_amenities', 'amenities');
        }
        if (Schema::hasTable('room_room_amenities')) {
            Schema::rename('room_room_amenities', 'room_amenities');
        }
    }
};
