<?php

namespace LastCall\DownloadsPlugin\Installer;

use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;

interface ExecutableInstallerInterface
{
    public function install(ExtraDownloadInterface $extraDownload): void;
}
