<?php

namespace LastCall\DownloadsPlugin\Composer\Repository;

use Composer\Repository\InstalledRepositoryInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;

interface ExtraDownloadsRepositoryInterface extends InstalledRepositoryInterface
{
    public function isTracked(ExtraDownloadInterface $extraDownload): bool;
}
