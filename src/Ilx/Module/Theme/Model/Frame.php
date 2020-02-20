<?php


namespace Ilx\Module\Theme\Model;



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