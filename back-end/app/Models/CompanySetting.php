<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'nom_entreprise', 'logo_path', 'email', 'telephone',
        'adresse', 'ville', 'nif_stat', 'devise', 'conditions_paiement',
    ];


}