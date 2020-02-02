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
     * @var boolean
     */
    private $overwrite;

    /**
     * @var boolean
     */
    private $is_dir;

    /**
     * Resource constructor.
     * @param string $path
     * @param string $module_name
     * @param string $copy
     * @param bool $overwrite
     * @param bool $is_dir
     */
    public function __construct(string $path, ?string $module_name, string $copy, bool $overwrite, bool $is_dir)
    {
        $this->path = $path;
        $this->module_name = $module_name;
        $this->copy_type = $copy;
        $this->overwrite = $overwrite;
        $this->is_dir = $is_dir;
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

    /**
     * @return bool
     */
    public function isOverwrite(): bool
    {
        return $this->overwrite;
    }

    /**
     * @return bool
     */
    public function isDirectory(): bool
    {
        return $this->is_dir;
    }
}