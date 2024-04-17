<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Package\PackageInterface;

interface PackageHandlerInterface
{
    public function handle(PackageInterface $package): void;
}
