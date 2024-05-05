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
        Schema::create('shave_token_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable(); // Memperbaiki kesalahan pengetikan dari 'unsigneadBigInteger' menjadi 'unsignedBigInteger'
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('tanggal_pembayaran');
            $table->enum('token_type', ['reguler', 'vip']);
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shave_token_purchases');
    }
};
