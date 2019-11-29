<?php


namespace Ilx\Module\Frame;


use Kodiak\Application;
use Kodiak\ServiceProvider\TwigProvider\ContentProvider\ContentProvider;

class FrameContentProvider extends ContentProvider
{
    public function getValue()
    {
        return Application::get("frame");
    }
}