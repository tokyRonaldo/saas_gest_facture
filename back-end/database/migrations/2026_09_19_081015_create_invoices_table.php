<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable()->unique();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('user_id')->constrained(); // créateur
            $table->enum('statut', ['brouillon', 'envoyee', 'payee', 'annulee'])->default('brouillon');
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->decimal('sous_total_ht', 14, 2)->default(0);
            $table->decimal('total_tva', 14, 2)->default(0);
            $table->decimal('total_ttc', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};