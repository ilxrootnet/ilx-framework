<?php


namespace Ilx\Module\Frame;


use Ilx\Module\Frame\Model\Frame;
use Kodiak\Application;
use Kodiak\ServiceProvider\TwigProvider\ContentProvider\ContentProvider;

class FrameContentProvider extends ContentProvider
{
    public function getValue()
    {
        /** @var Frame $frame */
        $frame = Application::get("frame");
        $frame->setActiveFrame($this->getActiveFrameName());
        return $frame;
    }

    public function getActiveFrameName() {
        return $this->getConfiguration()["active_frame"];
    }
}