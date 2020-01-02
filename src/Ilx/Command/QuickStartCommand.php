<?php


namespace Ilx\Command;


use GetOpt\Command;
use GetOpt\GetOpt;
use Ilx\Module\Security\SecurityModule;
use Kodiak\Core\KodiConf;

/**
 * Class QuickStartCommand
 *
 * A parancs segítséget nyújt a modules.json leíró összeállításában. A modules.json tartalmazza a szükséges információkat
 * az Ilx-Framework telepítéséhez.
 *
 * A parancs futtatása során végighalad azokon a fő elemeken ami szükséges lehet az alkalmazás működéséhez és a megadott
 * inputok alapján készíti el a modules.json-t.
 *
 * Például:
 *  php bin/ilx.php quick-start
 *
 * Kötelező paraméter:
 *  nincsen
 *
 * Opcionális paraméterek:
 *  nincsen
 *
 * Kötelező kapcsolók:
 *  nincsen
 *
 * Opcionális kapcsolók:
 *  nincsen
 *
 * @package Ilx\Command
 */
class QuickStartCommand extends Command
{

    public function __construct()
    {
        parent::__construct('quick-start', [$this, 'handle']);
    }

    public function handle(GetOpt $getOpt)
    {
        print("\nIlx-Framework quick-start\n===========================\n");


        if(file_exists("modules.json")) {
            print("A modules.json file has been detected. Are you sure you want to run quick-start? The modules.json will be overwritten! [yes or no]: ");
            if(!$this->isYes(false)) {
                return;
            }
        }

        $ilx_modules = [
            "modules" => []
        ];

        print("Project name: ");
        $ilx_modules["name"] = $this->readLine();

        print("Development mode [default=yes]: ");
        if($this->isYes()) {
            $ilx_modules["mode"] = KodiConf::ENV_DEVELOPMENT;
        }

        print("Do you want logging [default=yes]: ");
        if($this->isYes()) {
            $ilx_modules["modules"]["Logger"] = [];
        }

        print("Do you have database connection [default=yes]: ");
        if($this->isYes()) {
            $ilx_modules["modules"]["Database"] = [];

            print("Hostname: ");
            $ilx_modules["modules"]["Database"]["host"] = $this->readLine();

            print("Port: ");
            $ilx_modules["modules"]["Database"]["port"] = intval($this->readLine());

            print("Database name: ");
            $ilx_modules["modules"]["Database"]["dbname"] = $this->readLine();

            print("Username: ");
            $ilx_modules["modules"]["Database"]["user"] = $this->readLine();

            print("Password: ");
            $ilx_modules["modules"]["Database"]["password"] = $this->readLine();

            // silent, warning, exception
            print("PDO Error mode [silent, warning, exception; default=exception]: ");
            $line = $this->readLine();
            $ilx_modules["modules"]["Database"]["attributes"] = [];
            switch ($line) {
                case "\n":
                case "exception":
                    $ilx_modules["modules"]["Database"]["attributes"][\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
                    break;
                case "warning":
                    $ilx_modules["modules"]["Database"]["attributes"][\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_WARNING;
                    break;
                case "silent":
                    $ilx_modules["modules"]["Database"]["attributes"][\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_SILENT;
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown attribute: $line");
            }
        }

        $ilx_modules["modules"]["Twig"] = [];
        $ilx_modules["modules"]["Resource"] = [];

        print("Do you have smtp connection [default=yes]: ");
        if($this->isYes()) {
            $ilx_modules["modules"]["Mailer"] = [];

            print("Hostname: ");
            $ilx_modules["modules"]["Mailer"]["host"] = $this->readLine();

            print("Port: ");
            $ilx_modules["modules"]["Mailer"]["port"] = intval($this->readLine());

            print("Username: ");
            $ilx_modules["modules"]["Mailer"]["user"] = $this->readLine();

            print("Password: ");
            $ilx_modules["modules"]["Mailer"]["password"] = $this->readLine();

            print("Auth_mode [plain, login, cram-md5, or null; default=null]: ");
            $line = $this->readLine();
            switch ($line) {
                case "\n":
                case "null":
                    $ilx_modules["modules"]["Mailer"]["auth_mode"] = null;
                    break;
                case "plain":
                    $ilx_modules["modules"]["Mailer"]["auth_mode"] = "plain";
                    break;
                case "login":
                    $ilx_modules["modules"]["Mailer"]["auth_mode"] = "login";
                    break;
                case "cram-md5":
                    $ilx_modules["modules"]["Mailer"]["auth_mode"] = "cram-md5";
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown auth_mode: $line");
            }

            print("Encryption [tls, ssl, or null; default=null]: ");
            $line = $this->readLine();
            switch ($line) {
                case "\n":
                case "null":
                    $ilx_modules["modules"]["Mailer"]["encryption"] = null;
                    break;
                case "plain":
                    $ilx_modules["modules"]["Mailer"]["encryption"] = "tls";
                    break;
                case "login":
                    $ilx_modules["modules"]["Mailer"]["encryption"] = "ssl";
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown auth_mode: $line");
            }

            $ilx_modules["modules"]["Mailer"]["source"] = [];
            print("Name of the source address: ");
            $ilx_modules["modules"]["Mailer"]["source"]["name"] = $this->readLine();

            print("Email address of the source address: ");
            $ilx_modules["modules"]["Mailer"]["source"]["address"] = $this->readLine();
        }

        print("Do you need session [default=yes]");
        if($this->isYes()) {
            $ilx_modules["modules"]["Session"] = [];
        }

        print("Do you need user authentication/authorization [default=yes]: ");
        if($this->isYes()) {
            if(!array_key_exists("Session", $ilx_modules["modules"])) {
                $ilx_modules["modules"]["Session"] = [];
            }
            $ilx_modules["modules"]["Security"] = [];

            print("Admin email: ");
            $ilx_modules["modules"]["Security"]["admin"] = $this->readLine();

            print("Authentication type [password, two_factor, jwt; default=password]: ");
            switch ($this->readLine()) {
                case "password":
                    $ilx_modules["modules"]["Security"]["type"] = SecurityModule::AUTH_BASIC;
                    break;
                case "two_factor":
                    $ilx_modules["modules"]["Security"]["type"] = SecurityModule::AUTH_TWO_FACT;
                    break;
                case "jwt":
                    $ilx_modules["modules"]["Security"]["type"] = SecurityModule::AUTH_JWT;
                    break;
                default:
                    print("Unknown auth type! Auth type is set to password");
                    $ilx_modules["modules"]["Security"]["type"] = SecurityModule::AUTH_BASIC;
            }

            print("Enabled registration [default=yes]: ");
            if($this->isYes()) {
                $ilx_modules["modules"]["Security"]["registration"] = true;
            } else {
                $ilx_modules["modules"]["Security"]["registration"] = false;
            }
        }

        print("Generating project templates [default=yes]: ");
        if($this->isYes()) {
            @mkdir('src');
            @mkdir('src'.DIRECTORY_SEPARATOR.ucfirst($ilx_modules["name"]));
            @mkdir('src'.DIRECTORY_SEPARATOR.ucfirst($ilx_modules["name"]).DIRECTORY_SEPARATOR."Controller");
            @mkdir('src'.DIRECTORY_SEPARATOR.ucfirst($ilx_modules["name"]).DIRECTORY_SEPARATOR."Model");
            @mkdir('src'.DIRECTORY_SEPARATOR.ucfirst($ilx_modules["name"]).DIRECTORY_SEPARATOR."View");

            $content = $this->generatingModuleClass($ilx_modules["name"]);
            file_put_contents('src'.DIRECTORY_SEPARATOR.ucfirst($ilx_modules["name"]).
                DIRECTORY_SEPARATOR.ucfirst($ilx_modules["name"])."Module.php", $content);
        }
        $ilx_modules["modules"][ucfirst($ilx_modules["name"])] = [];

        print("Thanks! module.json has been created. To install the application execute the following command: php bin/ilx.php install module.json\n");

        file_put_contents('modules.json', json_encode($ilx_modules, JSON_PRETTY_PRINT));
    }

    private function readLine() {
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if(strlen($line) > 1) {
            $line = substr($line, 0, -1);
        }
        fclose($handle);
        return $line;
    }

    private function isYes($include_enter = True) {
        $line = $this->readLine();
        if ($include_enter) return $line === "yes" || $line === "y" || $line === "\n";
        else return $line === "yes" || $line === "y";
    }

    private function generatingModuleClass($project_name) {
        $loader = new \Twig\Loader\ArrayLoader([
            'module' => file_get_contents(__DIR__.DIRECTORY_SEPARATOR."Template".DIRECTORY_SEPARATOR."ModuleTemplate.text"),
        ]);
        $twig = new \Twig\Environment($loader);

        return $twig->render('module', [
            'project_name'  => ucfirst($project_name),
            'namespace'     => ucfirst($project_name)
        ]);
    }
}