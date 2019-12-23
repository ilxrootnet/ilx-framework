<?php


namespace Ilx\Module\Frame\Themes;

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
     * View útvonal
     * @return string
     */
    public function getViewPath() {
        return $this->getSourcePath().DIRECTORY_SEPARATOR."views";
    }

    /**
     * Js útvonal
     * @return string
     */
    public function getJsPath() {
        return $this->getSourcePath().DIRECTORY_SEPARATOR."js";
    }

    /**
     * Css útvonal
     * @return string
     */
    public function getCssPath() {
        return $this->getSourcePath().DIRECTORY_SEPARATOR."css";
    }

    /**
     * Images útvonal
     * @return string
     */
    public function getImagesPath() {
        return $this->getSourcePath().DIRECTORY_SEPARATOR."images";
    }
}