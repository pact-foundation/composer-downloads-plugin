<?php

namespace LastCall\DownloadsPlugin\Composer\Package\Tracking;

interface TrackingInterface
{
    public function getChecksum(): string;
}
