<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('item_images')) {
            Schema::create('item_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->string('path');
                $table->string('position');
                $table->string('hasil_deteksi');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('item_images');
    }
};
