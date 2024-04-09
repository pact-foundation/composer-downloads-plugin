<?php

namespace LastCall\DownloadsPlugin\Composer\Package;

interface ExtraArchiveInterface extends ExtraDownloadInterface
{
    public function clean(): void;
}
