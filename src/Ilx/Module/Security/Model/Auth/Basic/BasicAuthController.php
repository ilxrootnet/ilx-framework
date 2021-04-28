<?php


namespace Ilx\Module\Security\Model\Auth\Basic;


use Exception;
use Ilx\Module\Mailer\Mailer;
use Ilx\Module\Security\Model\AuthTheme;
use Ilx\Module\Security\Model\User;
use Ilx\Module\Theme\Model\Frame;
use Kodiak\Application;
use Kodiak\Exception\Http\HttpAccessDeniedException;
use Kodiak\Exception\Http\HttpInternalServerErrorException;
use Kodiak\Request\RESTRequest;
use Kodiak\Response\JsonResponse;
use Kodiak\Response\RESTResponse;
use Kodiak\Security\Model\Authentication\AuthenticationRequest;
use Kodiak\Security\Model\SecurityManager;
use Kodiak\ServiceProvider\TwigProvider\Twig;
use Monolog\Logger;
use PandaBase\Exception\AccessDeniedException;

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
     * @throws HttpInternalServerErrorException
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
     * @return JsonResponse
     * @throws HttpAccessDeniedException
     * @throws HttpInternalServerErrorException
     */
    public function register($params) {
        $authResult = $this->securityManager->handleAuthenticationRequest(
            new AuthenticationRequest(AuthenticationRequest::REQ_REGISTER,[
                "username"  => $_POST["username"],
                "email"     => $_POST["email"],
                "firstname" => $_POST["firstname"],
                "lastname"  => $_POST["lastname"],
                "password"  => $_POST["password"],
                "repassword"=> $_POST["repassword"]
            ])
        );

        if($authResult->isSuccess()) {
            /** @var User $user */
            $user = $authResult->getResult();
        }

        return new JsonResponse([
            "success" => $authResult->isSuccess(),
            "msg" => $authResult->getResult()
        ]);
    }

    /**
     * Jelszó csere.
     *
     * @param array $params
     * @return RESTResponse
     */
    public function changePassword($params) {
        /** @var Logger $logger */
        $logger = Application::get('logger');
        $request = RESTRequest::read();
        if (isset($request["username"])){
            $user = User::getUserByUsername($request["username"]);
        }
        else $user = Application::get('security')->getUser();
        $user_id = $user["user_id"];
        try {
            $authResult = $this->securityManager->handleAuthenticationRequest(
                new AuthenticationRequest(AuthenticationRequest::REQ_CHANGE_PASS,[
                    "username"     => $user["username"],
                    "old_password" => $request["old_password"],
                    "password"     => $request["password"],
                    "repassword"   => $request["repassword"],
                ])
            );
            $logger->info("Password change for username ".$user["username"]." was ".($authResult->isSuccess() == true ? 'successful' : 'unsuccessful')." result:".$authResult->getResult());
            if ($authResult->isSuccess()) {
                return RESTResponse::success();
            } else {
                return RESTResponse::error($authResult->getResult());
            }
        }
        catch (Exception $e) {
            return RESTResponse::error($e->getMessage());
        }
    }

    /**
     * Jelszó csere
     * @param array $params
     * @return RESTResponse
     */
    public function passwordResetRequest($params) {
        /** @var Logger $logger */
        $logger = Application::get('logger');
        $request = RESTRequest::read();
        /** @var User $user */
        $user = User::getUserByEmail($request["email"]);
        if ($user->isValidUsername() and $user->isActive()) {
            try {
                $basicUser = BasicUserData::fromUserId($user["user_id"]);
                $reset_token = $basicUser->generateResetToken();
                $logger->info("Password reset request for username ".$user["username"]." was successful, email sent to: ".$user["email"]);
                /** @var Mailer $mailer */
                $mailer = Application::get("mailer");
                $mailer->send(BasicAuthenticationMode::MAIL_PASSWORD_RESET, $user, [
                    "reset_url"  => "https://".$_SERVER["HTTP_HOST"].Application::get('url_generator')->generate('renderPasswordResetPage', [
                        "token" => $reset_token
                        ]),
                    "server_url" => "https://".$_SERVER["HTTP_HOST"]
                ]);
            } catch (\Exception $e) {
                // Logolni kell a hibát
                $logger->info("Password reset request for username ".$_POST["username"]." was unsuccessful, error: ".$e->getMessage());
            }
        }
        return RESTResponse::success();
    }

    /**
     * @param array $params
     * @return JsonResponse|RESTResponse
     */
    public function passwordReset($params) {
        try {
            $request = RESTRequest::read();
            if (!isset($request["password"]) || !isset($request["repassword"]) || !isset($request["token"])) return RESTResponse::error();
            $authResult = $this->securityManager->handleAuthenticationRequest(
                new AuthenticationRequest(AuthenticationRequest::REQ_RESET_PASS, [
                    "token" => $request["token"],
                    "password" => $request["password"],
                    "repassword" => $request["repassword"],
                ])
            );
            /** @var Logger $logger */
            $logger = Application::get('logger');
            $logger->info("Password reset for username " . $_POST["username"] . " was " . ($authResult->isSuccess() == true ? 'successful' : 'unsuccessful'));
            if ($authResult->isSuccess()) {
                return RESTResponse::success();
            } else {
                return RESTResponse::error();
            }
        }
        catch(\Exception $e) {
            return new JsonResponse([
                "success" => false,
                "msg" => $e->getMessage()
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

    /**
     * Email cím verifikáció generálása.
     *
     * @param $params
     * @return JsonResponse
     */
    public function sendVerificationAddress($params) {
        try {
            $user_id = $params["user_id"];

            $user = new User($user_id);
            $basicUser = BasicUserData::fromUserId($user_id);
            /** @var Mailer $mailer */
            $mailer = Application::get("mailer");
            $mailer->send(
                BasicAuthenticationMode::MAIL_REG_CONFIRMATION,
                $user,
                [
                    "user_id" => $user_id,
                    "token" => $basicUser->generateVerificationToken(),
                    "firstname" => $user["firstname"],
                    "lastname" => $user["lastname"],
                ]);

            return new JsonResponse([
                "success" => true,
                "msg" => "Email verification token has been set.",
            ]);

        } catch (Exception $e) {
            return new JsonResponse([
                "success" => false,
                "msg" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Email cím verifikációja.
     *
     * @param $params
     * @return string
     * @throws HttpInternalServerErrorException
     */
    public function verifyEmailAddress($params) {
        try {
        $user_id = $_GET["user_id"];
        $token = $_GET["token"];

        $basicUser = BasicUserData::fromUserId($user_id);

        $result = $basicUser->verifyEmailToken($token);

        /** @var Twig $twig */
        $twig = Application::get("twig");
        /** @var Frame $frame */
        $frame = Application::get("frame");
        /** @var AuthTheme $theme */
        $theme = $frame->getAuthenticationTheme();

        if($result) {
            return $twig->render($theme->getVerifiedEmailTemplate(), [], false, $theme->getFrame());
        }
        return $twig->render($theme->getUnVerifiedEmailTemplate(), [], false, $theme->getFrame());

        } catch (Exception $e) {
            throw new HttpInternalServerErrorException("Unknown user or wrong verfification token");
        }
    }
}