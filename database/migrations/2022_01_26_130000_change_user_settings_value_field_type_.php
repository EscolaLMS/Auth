<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserSettingsValueFieldType extends Migration
{
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->text('value')->change();
        });
    }

    public function down()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('value')->change();
        });
    }
}
