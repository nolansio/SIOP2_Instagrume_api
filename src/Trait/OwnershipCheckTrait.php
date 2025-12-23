<?php

namespace App\Trait;

trait OwnershipCheckTrait
{
    /**
     * Vérifie si le contenu appartient à l'utilisateur courant
     */
    private function isOwnContent($contentAuthor): bool
    {
        return $this->getUser()->getUserIdentifier() === $contentAuthor->getUserIdentifier();
    }
}
