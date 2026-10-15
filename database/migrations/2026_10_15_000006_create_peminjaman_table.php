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
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('barang_id', 36);
            $table->string('user_id', 36);
            $table->string('nama_peminjam');
            $table->string('ruangan_tujuan');
            $table->dateTime('tanggal_pinjam');
            $table->dateTime('tanggal_kembali')->nullable();
            $table->dateTime('deadline_kembali');
            $table->enum('status', ['AKTIF', 'SELESAI'])->default('AKTIF')->index();
            $table->boolean('terlambat')->default(false)->index();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
