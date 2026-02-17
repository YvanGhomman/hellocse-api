<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'image',
        'statut',
    ];

    // Constantes pour les statuts
    const STATUT_INACTIF = 'inactif';
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_ACTIF = 'actif';

    // Liste des statuts valides
    public static function getStatuts(): array
    {
        return [
            self::STATUT_INACTIF,
            self::STATUT_EN_ATTENTE,
            self::STATUT_ACTIF,
        ];
    }
}