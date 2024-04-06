<?php

namespace LastCall\DownloadsPlugin\Composer\Package\InstallPath;

use Composer\InstalledVersions;
use Composer\Package\PackageInterface;

class InstallPath implements InstallPathInterface
{
    public function __construct(private PackageInterface $package)
    {
    }

    public function convertToAbsolute(string $relative): string
    {
        return $this->getParentPath().\DIRECTORY_SEPARATOR.$relative;
    }

    private function getParentPath(): string
    {
        return InstalledVersions::getInstallPath($this->package->getName());
    }
}
