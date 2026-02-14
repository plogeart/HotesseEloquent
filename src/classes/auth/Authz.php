<?php

namespace projet\classes\auth;

use projet\classes\exception\AuthnException;

class Authz {

    public static function checkRoleGestionnaire(): void {
        $user = AuthnProvider::getSignedInUser();

        if ($user['grade'] !== 'gestionnaire') {
            throw new AuthnException("Accès refusé : rôle 'gestionnaire' requis.");
        }
    }

    public static function checkRoleHotesse(): void {
        if (!AuthnProvider::isSignedIn()) {
            throw new AuthnException("Accès refusé : connexion requise.");
        }
    }
}