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
            $table->string('first_name')->after('name')->nullable();
            $table->string('phone', 15)->after('email_verified_at')->unique()->nullable();
            $table->string('phone_verified_at')->nullable()->after('phone');
            $table->string('address')->after('password')->nullable();
            $table->string('picture')->after('address')->nullable();
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
