<?php


namespace Ilx\Module\Frame\Model;


use Ilx\Module\Frame\FrameModule;

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
     * Frame név -> téma név összerendelést tartalmaz
     * @var array
     */
    private $frames;

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
        $this->frames = isset($configuration["frames"]) ? $configuration["frames"] : [];
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
     * @return array
     */
    public function getStylesheets(): array
    {
        $active_theme = $this->frames[$this->active_frame];
        return isset($this->stylesheets[$active_theme]) ? $this->stylesheets[$active_theme] : [];
    }

    /**
     * @return array
     */
    public function getJavascripts(): array
    {
        $active_theme = $this->frames[$this->active_frame];
        return isset($this->javascripts[$active_theme]) ? $this->javascripts[$active_theme] : [];
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        $active_theme = $this->frames[$this->active_frame];
        return isset($this->images[$active_theme]) ? $this->images[$active_theme] : [];
    }


}