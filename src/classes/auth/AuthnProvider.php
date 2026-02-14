<?php

namespace projet\classes\auth;

use projet\models\Hotesse;
use projet\classes\exception\AuthnException;

class AuthnProvider {

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

        $_SESSION['user'] = [
            'id' => $hotesse->numhot,
            'nom' => $hotesse->nomserv,
            'grade' => $hotesse->grade
        ];
    }

    public static function isSignedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user']);
    }

    public static function getSignedInUser(): array {
        if (!self::isSignedIn()) {
            throw new AuthnException("Utilisateur non connecté.");
        }

        return $_SESSION['user'];
    }

    public static function signout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['user']);
        session_destroy();
    }
}