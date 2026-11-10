<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel users (Unique karena 1 user = 1 profile)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            
            // Identitas Pelari
            $table->string('fullName');
            $table->string('bibName');
            $table->enum('gender', ['male', 'female']);
            $table->date('dateOfBirth');
            $table->string('bloodType')->nullable();
            $table->string('nationality', 50)->nullable();
            
            // Kontak & Alamat
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postalCode')->nullable();
            
            // Data Identitas Resmi (KTP / SIM / Passport)
            $table->string('identity_type')->nullable();
            $table->string('identity_number')->nullable();
            
            // Profil Lari (ITRA / UTMB)
            $table->string('itraId')->nullable();
            $table->string('utmbId')->nullable();
            
            // Kontak Darurat
            $table->string('emergency_name')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_relationship')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};