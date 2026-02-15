<?php
namespace projet\classes\action;

use projet\classes\auth\AuthnProvider;

class LogoutAction extends Action {

    public function executeGet(): string {
        AuthnProvider::signout();

        $action = new LoginAction();
        return $action->execute();
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}