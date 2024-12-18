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
        Schema::table('users', function (Blueprint $table) {
            //
            // $table->string('phone', 15)->after('email')->unique();
            // $table->string('phone_verified_at')->nullable()->after('phone');
            $table->string('provider')->default('ITH_Auth_Service')->after('email_verified_at');
            // Add your custom column here
            // Example: $table->string('provider')->after('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
