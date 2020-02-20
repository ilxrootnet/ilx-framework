<?php


namespace Ilx\Module\Security\Controller;


use Ilx\Module\Theme\Model\Frame;
use Kodiak\Application;
use Kodiak\Request\RESTRequest;
use Kodiak\Response\RESTResponse;
use Kodiak\Security\Model\Authentication\AuthenticationMode;
use Kodiak\Security\Model\SecurityManager;
use Kodiak\ServiceProvider\TwigProvider\Twig;

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

    public function renderLogin() {
        /** @var Twig $twig */
        $twig = Application::get("twig");
        /** @var Frame $frame */
        $frame = Application::get("frame");
        $theme = $frame->getAuthenticationTheme();
        return $twig->render($theme->getLoginForm(), [], false, $theme->getFrame());
    }
}