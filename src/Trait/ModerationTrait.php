<?php

namespace App\Trait;

trait ModerationTrait
{
    /**
     * Vérifie si l'utilisateur courant peut modérer l'utilisateur cible
     * Règles :
     * - L'utilisateur doit être MOD ou ADMIN
     * - On ne peut pas se modérer soi-même
     * - Un MOD ne peut pas modérer un autre MOD ou un ADMIN
     * - Personne ne peut modérer un ADMIN
     */
    private function canModerateUser($targetUser): bool
    {
        $currentUser = $this->getUser();

        $currentRoles = $currentUser->getRoles();
        $isMod = in_array('ROLE_MOD', $currentRoles);
        $isAdmin = in_array('ROLE_ADMIN', $currentRoles);

        if (!$isMod && !$isAdmin) {
            return false;
        }

        if ($currentUser->getUserIdentifier() === $targetUser->getUserIdentifier()) {
            return false;
        }

        $targetRoles = $targetUser->getRoles();
        $targetIsMod = in_array('ROLE_MOD', $targetRoles);
        $targetIsAdmin = in_array('ROLE_ADMIN', $targetRoles);

        if ($targetIsAdmin) {
            return false;
        }

        if ($isMod && !$isAdmin && $targetIsMod) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si l'utilisateur courant est modérateur ou administrateur
     */
    private function isModerator(): bool
    {
        $roles = $this->getUser()->getRoles();
        return !empty(array_intersect($roles, ['ROLE_MOD', 'ROLE_ADMIN']));
    }
}
