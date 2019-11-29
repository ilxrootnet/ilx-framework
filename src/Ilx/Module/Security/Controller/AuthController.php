<?php


namespace Ilx\Module\Security\Controller;


use Kodiak\Application;
use Kodiak\Response\RESTResponse;

class AuthController
{
    /**
     * Visszaadja az aktuálisan érvényes authentikációs módo(ka)t.
     *
     * @param $params
     * @return RESTResponse
     */
    public function getAuthDialect($params) {
        return new RESTResponse(true, [
            "dialect" => Application::getEnv("auth_dialect")
        ]);
    }
}