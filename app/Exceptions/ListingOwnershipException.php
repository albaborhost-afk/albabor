<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Transfert ou attribution d'annonce refusé (compte administrateur, compte
 * bloqué…). Le message est en français et destiné à l'administrateur.
 */
class ListingOwnershipException extends RuntimeException
{
}
