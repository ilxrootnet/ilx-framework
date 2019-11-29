<?php


namespace Ilx\Module\Resource;


class ResourcePath
{
    const SOFT_COPY = "soft";
    const HARD_COPY = "hard";

    /**
     * @var string
     */
    private $path;

    /**
     * @var
     */
    private $module_name;

    /**
     * @var string
     */
    private $copy_type;

    /**
     * Resource constructor.
     * @param string $path
     * @param string $module_name
     * @param string $copy
     */
    public function __construct(string $path, ?string $module_name, string $copy)
    {
        $this->path = $path;
        $this->module_name = $module_name;
        $this->copy_type = $copy;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->module_name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getCopyType(): string
    {
        return $this->copy_type;
    }


}