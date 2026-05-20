<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'subscriber')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $after = Schema::hasColumn('users', 'msisdn') ? 'msisdn' : 'email';
            $table->string('subscriber', 64)->nullable()->after($after);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'subscriber')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscriber');
        });
    }
};
