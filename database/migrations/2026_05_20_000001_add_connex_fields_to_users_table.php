<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Connex login-confirm success payload (messageCode 00):
 * msisdn, subscriber, status, operator, expiration_date
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'msisdn')) {
                $table->string('msisdn', 32)->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'subscriber')) {
                $table->string('subscriber', 64)->nullable()->after('msisdn');
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 32)->nullable()->after('subscriber');
            }
            if (! Schema::hasColumn('users', 'operator')) {
                $table->string('operator', 64)->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'expiration_date')) {
                $table->dateTime('expiration_date')->nullable()->after('operator');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'expiration_date') ? 'expiration_date' : null,
                Schema::hasColumn('users', 'operator') ? 'operator' : null,
                Schema::hasColumn('users', 'status') ? 'status' : null,
                Schema::hasColumn('users', 'subscriber') ? 'subscriber' : null,
                Schema::hasColumn('users', 'msisdn') ? 'msisdn' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
