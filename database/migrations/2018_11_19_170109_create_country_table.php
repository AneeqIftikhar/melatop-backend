<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 'ET', 'ETHIOPIA', 'Ethiopia', 'ETH', 231, 251
        Schema::create('country', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name','80');
            $table->string('iso','2');
            $table->string('nickname','80');
            $table->integer('numcode');
            $table->integer('phonecode');

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
        Schema::dropIfExists('country');
    }
}
