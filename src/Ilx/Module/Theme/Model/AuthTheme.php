<?php


namespace Ilx\Module\Theme\Model;

/**
 * Interface AuthTheme
 *
 * Az interfész feladata, hogy definiáljon egy olyan addicionális téma interfészt, amellyel egy téma arra is alkalmas, hogy
 * egy authentikációs folyamat téma elemeit (viewkat) is biztosítson
 *
 *
 * @package Ilx\Module\Theme\Themes
 */
interface AuthTheme
{
    /**
     * Visszaadja az authentikációnál használatos
     * @return string
     */
    public function getFrame();

    /**
     * Login form-ot leíró twig fáj elérési útvonala.
     * @return string
     */
    public function getLoginForm();

    /**
     * Regisztrációs form-ot leíró twig fáj elérési útvonala.
     * @return string
     */
    public function getRegistrationForm();


}