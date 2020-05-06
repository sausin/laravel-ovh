<?php

namespace Sausin\LaravelOvh\Composer;

class Scripts
{
    /*
     * Stops propagation of Composer event if isn't on development environment
     *
     * This is useful to prevent running scripts for third-party packages
     * without the need to intercept previous event scripts that should be run
     * during non-development environments as well.
     */
    public static function devOnly($event)
    {
        if (!$event->isDevMode()) {
            $event->stopPropagation();
        }
    }
}
