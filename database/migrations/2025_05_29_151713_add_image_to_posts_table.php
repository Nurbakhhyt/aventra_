<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // 'image' бағанын қосу
            // Сурет жолын сақтау үшін string типті баған
            $table->string('image')->nullable()->after('content'); // 'content' бағанынан кейін қосу
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Миграцияны кері қайтарғанда бағанды жою
            $table->dropColumn('image');
        });
    }
};
