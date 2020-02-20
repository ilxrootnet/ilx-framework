<?php


namespace Ilx\Module\Theme\Model;



use Ilx\Module\Theme\Themes\AuthTheme;
use Ilx\Module\Theme\Themes\Theme;

class Frame
{
    /**
     * @var string
     */
    private $active_frame;

    /**
     * @var string
     */
    private $title;

    /**
     * Authentikációs téma neve.
     * @var Theme
     */
    private $auth_theme;

    /**
     * Frame név -> téma név összerendelést tartalmaz
     * @var array
     */
    private $themes;

    /**
     * Frame constructor.
     * @param array $configuration
     */
    public function __construct($configuration)
    {
        $this->title = $configuration["title"];
        $this->auth_theme = $configuration["auth_theme"];
    }

    public function setActiveFrame($frame_name) {
        $this->active_frame = $frame_name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return AuthTheme
     */
    public function getAuthenticationTheme(): AuthTheme
    {
        $cls_name = $this->auth_theme;
        return new $cls_name();
    }

}