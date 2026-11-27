<?php

namespace App\Enum;

enum StatutCommande: string
{
    case EN_COURS_PREPARATION = 'en cours de préparation';
    case PRETE = 'prête';
    case PAYEE = 'payée';
}
