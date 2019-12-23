<?php


namespace Ilx\Module\Frame\Model;



use Ilx\Module\Frame\Themes\Theme;

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
     * Css fájlok listája
     * @var array
     */
    private $stylesheets;

    /**
     * Js fájlok listája
     * @var array
     */
    private $javascripts;


    /**
     * Képek listája
     * @var array
     */
    private $images;

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
        $this->stylesheets = isset($configuration["stylesheets"]) ? $configuration["stylesheets"] : [];
        $this->javascripts = isset($configuration["javascripts"]) ? $configuration["javascripts"] : [];
        $this->images = isset($configuration["images"]) ? $configuration["images"] : [];
        $this->themes = isset($configuration["frames"]) ? $configuration["frames"] : [];
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
     * @param string $frame
     * @return array
     */
    public function getStylesheets($frame = null): array
    {
        if($frame == null) {
            $frame = $this->active_frame;
        }
        $active_theme = $this->themes[$frame];
        return isset($this->stylesheets[$active_theme]) ? $this->stylesheets[$active_theme] : [];
    }

    /**
     * @param string $frame
     * @return array
     */
    public function getJavascripts($frame = null): array
    {
        if($frame == null) {
            $frame = $this->active_frame;
        }
        $active_theme = $this->themes[$frame];
        return isset($this->javascripts[$active_theme]) ? $this->javascripts[$active_theme] : [];
    }

    /**
     * @param string $frame
     * @return array
     */
    public function getImages($frame = null): array
    {
        if($frame == null) {
            $frame = $this->active_frame;
        }
        $active_theme = $this->themes[$frame];
        return isset($this->images[$active_theme]) ? $this->images[$active_theme] : [];
    }

    /**
     * @return Theme
     */
    public function getAuthenticationTheme(): string
    {
        return $this->auth_theme;
    }

}