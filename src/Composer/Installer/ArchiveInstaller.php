<?php

namespace LastCall\DownloadsPlugin\Composer\Installer;

use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraArchiveInterface;
use LastCall\DownloadsPlugin\Enum\PackageType;
use React\Promise\PromiseInterface;

class ArchiveInstaller extends AbstractInstaller
{
    public function supports(string $packageType): bool
    {
        return $packageType === PackageType::ARCHIVE->value;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package): PromiseInterface
    {
        if (!$package instanceof ExtraArchiveInterface) {
            return \React\Promise\resolve(null);
        }

        $promise = $this->composer->getDownloadManager()->install($package, $package->getInstallPath());

        return $promise->then(function () use ($repo, $package) {
            $package->clean();

            return parent::install($repo, $package);
        });
    }
}
