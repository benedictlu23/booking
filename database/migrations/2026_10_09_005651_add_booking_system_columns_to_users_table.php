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
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'avatar')) {
                $table->text('avatar')->nullable()->after('google_id');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('customer')->after('avatar');
            }

            if (! Schema::hasColumn('users', 'visit_count')) {
                $table->unsignedInteger('visit_count')->default(0)->after('role');
            }

            if (! Schema::hasColumn('users', 'last_visited_at')) {
                $table->timestamp('last_visited_at')->nullable()->after('visit_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = ['google_id', 'avatar', 'role', 'visit_count', 'last_visited_at'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
