<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    protected $fillable = ['annee', 'dernier_numero'];
    public $timestamps = false;
}