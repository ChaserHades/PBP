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
        Schema::create('barang', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('nama_barang')->nullable();
            $table->string('progdi')->nullable();
            $table->string('label')->nullable()->index();
            $table->string('label_aset')->nullable()->index();
            $table->string('sn')->nullable()->index();
            $table->string('merek')->nullable();
            $table->string('model')->nullable();
            $table->string('tipe')->nullable();
            $table->string('vendor')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('kondisi', ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT'])->default('BAIK')->index();
            $table->enum('status', ['TERSEDIA', 'DIPINJAM', 'MAINTENANCE'])->default('TERSEDIA')->index();
            $table->integer('jumlah')->default(1);
            $table->dateTime('tanggal_datang')->nullable();
            $table->dateTime('tanggal_pindah')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->string('sumber_tahun')->nullable();
            $table->integer('pembelian_ke')->nullable();
            $table->boolean('has_sticker')->default(false);
            $table->longText('qr_code')->nullable();
            $table->string('foto')->nullable();
            $table->string('kategori_id', 36);
            $table->string('ruangan_id', 36)->nullable();
            $table->string('pengeluaran_id', 36)->nullable();
            $table->string('kelompok_pembelian_id', 36)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('ruangan_id')->references('id')->on('ruangan')->nullOnDelete();
            $table->foreign('pengeluaran_id')->references('id')->on('pengeluaran')->nullOnDelete();
            $table->foreign('kelompok_pembelian_id')->references('id')->on('kelompok_pembelian')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
