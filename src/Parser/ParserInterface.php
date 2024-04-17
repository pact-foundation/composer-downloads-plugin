<?php

namespace LastCall\DownloadsPlugin\Parser;

use Composer\Package\PackageInterface;

interface ParserInterface
{
    /**
     * @return PackageInterface[]
     */
    public function parse(PackageInterface $package): array;
}
