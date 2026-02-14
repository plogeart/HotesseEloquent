<?php
namespace projet\classes\action;

class LogoutAction extends Action {

    public function executeGet(): string {
        session_destroy();

        $action = new LoginAction();
        return $action->execute();
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}