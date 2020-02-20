<?php


namespace Ilx\Module\Theme\Model;

use Less_Parser;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

/**
 * Class Theme
 *
 * Definiálja a téma definíció
 *
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
     * Visszadja a frame-k tömbjét:
     *  frame_name => relatív útvonal a frames mappán belül
     *
     * @return array[]
     */
    public abstract function getFrameList();

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
     * Minified js útvonal
     * @return string
     */
    public function getMinifiedJsPath() {

        // Ellenőrizzük, hogy van-e js fájl
        if(count($this->getJsFiles()) < 1) {
            return null;
        }

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

        // Ellenőrizzük, hogy van-e style fájl
        if(count($this->getStyleFiles()) < 1) {
            return null;
        }


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
     * Témához tartozó egyéb forrásfájlok elérési útvonala.
     *
     * @return string
     */
    public static function getResourcesPath() {
        return self::getSourcePath().DIRECTORY_SEPARATOR."resources";
    }

    /**
     * Témához tartozó frame-k elérési útvonala.
     * @return string
     */
    public static function getFramesPath() {
        return self::getSourcePath().DIRECTORY_SEPARATOR."frames";
    }

    /**
     * A téma forrásfájljait tartalmazó mappa útvonala.
     *
     * @return string
     */
    public static function getSourcePath() {
        return __DIR__;
    }
}