<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('bookName',150)->required();
            $table->string('publication',150)->required();
            $table->string('author',150)->required(); 
            $table->string('isbn',13)->required();
            $table->string('image',125)->required();
            $table->unsignedInteger('price')->required();
            $table->unsignedInteger('stock')->required();
            $table->unsignedInteger('edition')->required();
            $table->year('year')->required();
            $table->boolean('negotiable')->required();
            $table->unsignedBigInteger('seller_id');
            $table->foreign('seller_id')->references('id')->on('users');
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
        Schema::dropIfExists('books');
    }
}
