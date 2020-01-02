<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Kodiak\Application;
use Kodiak\Exception\Http\HttpAccessDeniedException;
use Kodiak\Response\JsonResponse;
use Kodiak\Security\Model\Authentication\AuthenticationRequest;
use Kodiak\Security\Model\SecurityManager;
use Monolog\Logger;

class BasicAuthController
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->securityManager = Application::get("security");
    }

    /**
     * Felhasználó bejelentkeztetése.
     *
     * @param array $params
     * @return JsonResponse
     */
    public function login($params) {
        try {
            $authResult = $this->securityManager->handleAuthenticationRequest(
                new AuthenticationRequest(AuthenticationRequest::REQ_LOGIN,[
                    "username" => $_POST["username"],
                    "password" => $_POST["password"]
                ])
            );

            /** @var Logger $logger */
            $logger = Application::get('logger');
            $logger->info("Auth request with username ".$_POST["username"]." was ".($authResult->isSuccess() == true ? 'successful' : 'unsuccessful'));


            if ($authResult->isSuccess()) {
                return new JsonResponse([
                    "success" => true,
                    "msg" => $authResult->getResult(),
                ]);
            }
            else {
                return new JsonResponse([
                    "success" => false,
                    "msg" => $authResult->getResult(),
                ]);
            }
        } catch (HttpAccessDeniedException $e) {
            return new JsonResponse([
                "success" => false,
                "msg" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Felhasználó regisztrációja.
     *
     * @param array $params
     */
    public function register($params) {
        // TODO
    }

    /**
     * @param $params
     */
    public function changePassword($params) {
        // TODO
    }

    public function passwordResetRequest($params) {
        // TODO
    }

    public function passwordReset($params) {
        // TODO
    }

    public function logout($params) {
        // TODO
    }
}