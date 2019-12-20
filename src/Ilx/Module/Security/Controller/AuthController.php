<?php


namespace Ilx\Module\Security\Controller;


use Kodiak\Application;
use Kodiak\Request\RESTRequest;
use Kodiak\Response\RESTResponse;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\Security\Model\SecurityManager;

class AuthController
{
    /**
     * Visszadja a userhez tartozó érvényes authentikációs módot.
     *
     * @return RESTResponse
     */
    public function getAuthDialect() {

        $data = RESTRequest::read();
        if(!isset($data["username"])) {
            return RESTResponse::error("Missing username field");
        }
        $username = $data["username"];

        /** @var SecurityManager $securityManager */
        $securityManager = Application::get("security");
        /** @var AuthenticationMode $authMode */
        $authMode = $securityManager->getAuthMode($username);

        return RESTResponse::success([
           "dialect" => $authMode::name()
        ]);
    }
}