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
        Schema::create('package_banks', function (Blueprint $table) {
            $table->id();
            $table->string('ban_name');
            $table->string('bank_account_name');
            $table->string('bank_account_number');
            $table->softDeletes();
            $table->string('logo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_banks');
    }
};