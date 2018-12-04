<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('stories_id')->unsigned();
            $table->foreign('stories_id')->references('id')->on('stories')->onDelete('cascade');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->double('rate',8,4);
            $table->string('level','255')->nullable();
            $table->string('ip','255')->nullable();
            $table->string('platform','255')->nullable();//web/android/ios
            $table->string('browser','255')->nullable();
            $table->string('social_media','255')->nullable();
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
        Schema::dropIfExists('visits');
    }
}
