<?php

namespace projet\classes\action;


abstract class Action {

    protected ?string $http_method = null;

    public function __construct(){
        $this->http_method = $_SERVER['REQUEST_METHOD'];
    }

    public function execute() : string {
        if ($this->http_method === "POST") {
            return $this->executePost();
        } else {
            return $this->executeGet();
        }
    }

    public abstract function executeGet() : string;
    public abstract function executePost() : string;
}