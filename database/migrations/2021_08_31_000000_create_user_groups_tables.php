<?php

use EscolaLms\Core\Migrations\EscolaMigration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserGroupsTables extends EscolaMigration
{
    public function up()
    {
        $this->create('groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });

        $this->create('group_user', function (Blueprint $table) {
            $table->foreignId('group_id')->index();
            $table->foreignId('user_id')->index();

            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->primary(['user_id', 'group_id']);
        });
    }
}
