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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('judul');
            $table->string('nama_barang')->nullable();
            $table->string('progdi')->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('nominal', 15, 2);
            $table->dateTime('tanggal');
            $table->enum('jenis', ['PEMBELIAN_ASET', 'MAINTENANCE', 'LAINNYA']);
            $table->string('jenis_bukti')->nullable(); // SPB, KWITANSI, NOTA
            $table->string('nomor_bukti')->nullable();
            $table->json('nota_urls')->nullable();
            $table->string('kode_pembelian')->nullable();
            $table->integer('pembelian_ke')->nullable();
            $table->string('user_id', 36);
            $table->string('kelompok_pembelian_id', 36)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kelompok_pembelian_id')->references('id')->on('kelompok_pembelian')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
