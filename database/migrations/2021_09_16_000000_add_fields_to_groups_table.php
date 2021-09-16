<?php

use EscolaLms\Core\Migrations\EscolaMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToGroupsTable extends EscolaMigration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('registerable')->default(false);
            $table->foreignId('parent_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('registerable');
        });
    }
}
