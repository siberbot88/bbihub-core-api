<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicles', function (Blueprint $table) {
      $table->id();

      $table->uuid('customer_uuid');                 // sesuai yang kamu minta
      $table->string('code')->unique();              // kode unik
      $table->string('name');                        // nama kendaraan
      $table->string('type')->nullable();
      $table->string('category')->nullable();
      $table->string('brand')->nullable();
      $table->string('model')->nullable();
      $table->unsignedSmallInteger('year')->nullable();
      $table->string('color')->nullable();
      $table->string('plate_number', 20)->unique();  // plat unik
      $table->unsignedInteger('odometer')->nullable();

      $table->timestamps();

      // opsional: kalau customer_uuid mengarah ke users.id (uuid) atau customers table, bisa tambahin FK
      // $table->foreign('customer_uuid')->references('id')->on('users')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicles');
  }
};
