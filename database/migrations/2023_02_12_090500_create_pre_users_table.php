<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('pre_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('token');
            $table->string('provider');
            $table->string('provider_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_users');
    }
}
