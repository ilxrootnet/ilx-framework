<?php


namespace Ilx\Module\Frame\Themes;

use Less_Parser;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

/**
 * Class Theme
 *
 * Egy stílus téma definiálja, hogy:
 *  - view-kat
 *  - view-khoz tartozó css, js, images fájlokat
 *  - frame-ket (frame név => view relatív útvonal)
 *
 * Megjegyzés:
 * Egy stílus témán belül, mindegyik frame-be ugyanazt a js,css és images fájlokat tölti a be a rendszer. Ha ezeket
 * külön szeretnénk kezelni, akkor külön stílust kell definiálni.
 *
 * @package Ilx\Module\Frame\Themes
 */
abstract class Theme
{
    /**
     * Téma neve.
     *
     * @return string
     */
    public abstract function getName();

    /**
     * A téma forrásfájljait tartalmazó mappa útvonala.
     *
     * @return string
     */
    public abstract function getSourcePath();

    /**
     * Visszadja a frame-k tömbjét:
     *  frame_name => view relatív path
     *
     * @return array[]
     */
    public abstract function getFramesPath();

    /**
     * Visszadja a témához tartozó authentikációs frame twig relatív útvonalát.
     *
     * @return string
     */
    public abstract function getAuthenticationFrame();


    /**
     * login.twig relatív útvonala.
     *
     * @return string
     */
    public abstract function getLoginForm();

    /**
     * registration.twig relatív útvonala.
     * @return mixed
     */
    public abstract function getRegistrationForm();

    /**
     * Visszaadja a javascript fájlok tömbjét, amikből a minified javascript készül.
     *
     * @return array
     */
    public abstract function getJsFiles();


    /**
     * Visszaadja a css és/vagy less fájlok tömbjét, amikből a minified css készül.
     *
     * @return array
     */
    public abstract function getStyleFiles();


    /**
     * View útvonal
     * @return string
     */
    public function getViewPath() {
        return $this->getSourcePath().DIRECTORY_SEPARATOR."views";
    }

    /**
     * Minified js útvonal
     * @return string
     */
    public function getMinifiedJsPath() {
        // Könyvtár létrehozása
        $minified_dir = $this->getSourcePath().DIRECTORY_SEPARATOR."js";
        @mkdir($minified_dir);

        // Minifier betöltése és futtatása
        $minifier = new JS(...$this->getJsFiles());
        $minified_file_path = $minified_dir . DIRECTORY_SEPARATOR . strtolower($this->getName()) . ".min.js";
        $minifier->minify($minified_file_path);

        // Visszaadjuk a min.js útvonalat
        return $minified_file_path;
    }

    /**
     * Minified css útvonala
     * @return string
     */
    public function getMinifiedCssPath() {
        // Könyvtár létrehozás
        $minified_dir = $this->getSourcePath().DIRECTORY_SEPARATOR."css";
        @mkdir($minified_dir);

        // A less fájlokat lefordítjuk, ha találunk egyet
        $styles = $this->getStyleFiles();
        $css_files = [];
        for ($i = 0; $i < count($styles); $i++) {
            if(pathinfo($styles[$i])['extension'] === "less") {
                try {
                    $parser = new Less_Parser();
                    $parser->parseFile($styles[$i]);
                    $css = $parser->getCss();
                    file_put_contents(substr($styles[$i], 0, -4)."css", $css);

                }catch(\Exception $e){
                    print("\t\t[ERROR] An error occured during the parse of less file: $styles[$i] . Error message: ". $e->getMessage());
                    print("\n");
                    print("\t\t[WARNING] A less will be skipped from minified css: $styles[$i] \n");
                }
            } else {
                $css_files[] = $styles[$i];
            }
        }

        // Minifier betöltése és futtatása
        $minifier = new CSS(...$css_files);
        $minified_file_path = $minified_dir . DIRECTORY_SEPARATOR . strtolower($this->getName()) . ".min.css";
        $minifier->minify($minified_file_path);

        // Visszaadjuk a min.css útvonalat
        return $minified_file_path;
    }

    /**
     * Images útvonal
     * @return string
     */
    public function getImagesPath() {
        return $this->getSourcePath().DIRECTORY_SEPARATOR."images";
    }
}