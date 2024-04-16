<?php

namespace LastCall\DownloadsPlugin\Factory;

use Composer\Package\PackageInterface;

interface ExtraDownloadFactoryInterface
{
    public function create(string $id, PackageInterface $parent): PackageInterface;
}
