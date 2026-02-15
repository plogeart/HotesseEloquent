<?php

namespace projet\classes\auth;

use projet\models\Hotesse;
use projet\classes\exception\AuthnException;

/**
 * Class AuthnProvider
 */
class AuthnProvider {

    /**
     * Tente de connecter une hôtesse
     * @param int $id Identifiant
     * @param string $password Mot de passe
     * @throws AuthnException Si identifiants incorrects.
     */
    public static function signin(int $id, string $password): void {
        $hotesse = Hotesse::find($id);

        if (!$hotesse) {
            throw new AuthnException("Identifiant inconnu.");
        }

        if ($hotesse->passwd !== $password) {
            throw new AuthnException("Mot de passe incorrect.");
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        //stock des infos utilisateurs en session
        $_SESSION['user'] = [
            'id' => $hotesse->numhot,
            'nom' => $hotesse->nomserv,
            'grade' => $hotesse->grade
        ];
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     */
    public static function isSignedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    /**
     * Récupère les infos de l'utilisateur connecté.
     * @throws AuthnException Si non connecté.
     */
    public static function getSignedInUser(): array {
        if (!self::isSignedIn()) {
            throw new AuthnException("Utilisateur non connecté.");
        }
        return $_SESSION['user'];
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public static function signout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user']);
        session_destroy();
    }
}