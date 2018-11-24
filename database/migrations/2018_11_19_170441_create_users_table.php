<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name','100');
                $table->string('last_name','100');
                $table->string('email','100')->unique();
                $table->string('password','255');
                $table->string('phone','100');
                $table->string('status','25');
                $table->string('role','25');
                $table->string('level','25');
                $table->string('city','100')->nullable();
                $table->string('image','100')->nullable();
                $table->integer('country_id')->unsigned()->nullable();
                $table->foreign('country_id')->references('id')->on('country')->onDelete('cascade');
                $table->integer('state_id')->unsigned()->nullable();
                $table->foreign('state_id')->references('id')->on('state')->onDelete('cascade');
                $table->integer('bank_id')->unsigned()->nullable();
                $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
                $table->string('account','100')->nullable();
                $table->softDeletes();
                $table->rememberToken();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
