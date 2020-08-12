<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageTestTables extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->date('registered_at');
            $table->foreignId('country_id')->constrained();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code');
            $table->integer('items');
            $table->dateTime('shipped_at');
            $table->foreignId('client_id')->constrained();
            $table->timestamps();
        });

        Schema::create('favorite_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->timestamps();
        });

        Schema::create('client_favorite_product', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('favorite_product_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('favorite_product_id')->references('id')->on('favorite_products');
            $table->primary(['client_id', 'favorite_product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('clients');
    }
}
