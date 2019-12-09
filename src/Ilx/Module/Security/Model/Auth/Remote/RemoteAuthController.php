<?php


namespace Ilx\Module\Security\Model\Auth\Remote;


use Kodiak\Application;
use Kodiak\Exception\Http\HttpAccessDeniedException;
use Kodiak\Response\JsonResponse;
use Kodiak\Security\Model\Authentication\AuthenticationRequest;
use Kodiak\Security\Model\SecurityManager;
use Monolog\Logger;


class RemoteAuthController
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
     * Bejelentkezés művelet kezelése.
     *
     * @param $params
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
                    "success" => false,
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
     * Kijelentkezés művelet kezelése.
     *
     * @param $params
     * @return JsonResponse
     */
    public function logout($params) {
        try {
            /** @var Logger $logger */
            $logger = Application::get('logger');
            $user = $this->securityManager->getUser();
            $logger->info("Logout with username ".$user["username"]);

            session_unset();
            session_destroy();
            return new JsonResponse([
                "success" => true
            ]);
        }
        catch(\Exception $e) {
            return new JsonResponse([
                "success" => false,
                "msg" => $e->getMessage()
            ]);
        }
    }
}