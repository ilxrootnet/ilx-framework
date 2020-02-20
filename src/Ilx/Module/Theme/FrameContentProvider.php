<?php


/**
 * @deprecated
 * TODO: Erre valószínűleg nem lesz szükség a jövőben és hamarosan töröljük.
 *
 *
 */

namespace Ilx\Module\Theme;


use Ilx\Module\Theme\Model\Frame;
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