<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('hari')->after('tanggal')->nullable();

            // Simpan jam dalam format string "HH:ii" (contoh: 09:00)
            $table->string('waktu_mulai', 5)->change();
            $table->string('waktu_selesai', 5)->change();
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('hari');

            // Balikin ke tipe TIME kalau perlu rollback
            $table->time('waktu_mulai')->change();
            $table->time('waktu_selesai')->change();
        });
    }
};
