<?php

namespace LastCall\DownloadsPlugin\Composer\Package\Cleaner;

interface CleanerInterface
{
    public function clean(string $dir): void;
}
