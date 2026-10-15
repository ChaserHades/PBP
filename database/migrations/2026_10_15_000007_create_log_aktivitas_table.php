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
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->enum('aksi', ['CREATE', 'UPDATE', 'DELETE', 'IMPORT'])->index();
            $table->string('entitas')->default('BARANG')->index();
            $table->string('entitas_id', 36)->nullable()->index();
            $table->text('detail');
            $table->string('user_id', 36);
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
