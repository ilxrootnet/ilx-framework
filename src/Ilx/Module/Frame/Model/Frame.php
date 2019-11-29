<?php


namespace Ilx\Module\Frame\Model;


class Frame
{
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
     * Frame constructor.
     * @param array $configuration
     */
    public function __construct($configuration)
    {
        $this->title = $configuration["title"];
        $this->stylesheets = isset($configuration["stylesheets"]) ? $configuration["stylesheets"] : [];
        $this->javascripts = isset($configuration["javascripts"]) ? $configuration["javascripts"] : [];
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
        return $this->stylesheets;
    }

    /**
     * @return array
     */
    public function getJavascripts(): array
    {
        return $this->javascripts;
    }


}