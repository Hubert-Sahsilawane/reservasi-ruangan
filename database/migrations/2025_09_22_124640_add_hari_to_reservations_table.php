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

            // Ubah waktu_mulai & waktu_selesai ke tipe time (biar konsisten)
            $table->time('waktu_mulai')->change();
            $table->time('waktu_selesai')->change();
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('hari');

            $table->string('waktu_mulai')->change();
            $table->string('waktu_selesai')->change();
        });
    }
};
