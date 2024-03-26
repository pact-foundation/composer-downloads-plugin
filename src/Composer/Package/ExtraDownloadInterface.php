<?php

namespace LastCall\DownloadsPlugin\Composer\Package;

use Composer\Package\PackageInterface;

interface ExtraDownloadInterface extends PackageInterface
{
    public function getTrackingChecksum(): string;

    public function getInstallPath(): string;

    public function getExecutablePaths(): array;

    public function verifyFile(string $path): bool;
}
