<?php

namespace LastCall\DownloadsPlugin\Composer\Installer;

use Composer\Composer;
use Composer\Installer\InstallerInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Exception\ExtraDownloadHashMismatchException;
use LastCall\DownloadsPlugin\Installer\ExecutableInstaller;
use LastCall\DownloadsPlugin\Installer\ExecutableInstallerInterface;
use React\Promise\PromiseInterface;

abstract class AbstractInstaller implements InstallerInterface
{
    private ExecutableInstallerInterface $executableInstaller;

    public function __construct(
        private IOInterface $io,
        protected Composer $composer,
        ?ExecutableInstallerInterface $executableInstaller = null,
    ) {
        $this->executableInstaller = $executableInstaller ?? new ExecutableInstaller($this->io);
    }

    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package): bool
    {
        if (!$package instanceof ExtraDownloadInterface) {
            return false;
        }

        $hasPackage = $repo->hasPackage($package);
        $fileExists = file_exists($package->getInstallPath());

        if (!$hasPackage && $fileExists) {
            $this->io->write(
                sprintf(
                    '<info>Extra file <comment>%s</comment> has been locally overriden in <comment>%s</comment>. To reset it, delete and reinstall.</info>',
                    $package->getName(),
                    $package->getTargetDir()
                ),
                true
            );

            return true;
        }

        if ($hasPackage && $fileExists) {
            $this->io->write(
                sprintf('<info>Skip extra file <comment>%s</comment></info>', $package->getName()),
                true,
                IOInterface::VERY_VERBOSE
            );

            return true;
        }

        return false;
    }

    public function download(PackageInterface $package, ?PackageInterface $prevPackage = null): PromiseInterface
    {
        if (!$package instanceof ExtraDownloadInterface) {
            return \React\Promise\resolve(null);
        }

        $targetDir = \dirname($package->getInstallPath());
        $promise = $this->composer->getDownloadManager()->download($package, $targetDir);

        return $promise->then(function (string $result) use ($package, $targetDir) {
            if ($package->verifyFile($result)) {
                return \React\Promise\resolve($result);
            }
            $this->io->error(sprintf('    Extra file "%s" does not match hash value defined in "%s".', $package->getDistUrl(), $package->getName()));
            $this->composer->getDownloadManager()->cleanup('install', $package, $targetDir);

            return \React\Promise\reject(new ExtraDownloadHashMismatchException());
        });
    }

    public function prepare(string $type, PackageInterface $package, ?PackageInterface $prevPackage = null): PromiseInterface
    {
        return \React\Promise\resolve(null);
    }

    public function cleanup(string $type, PackageInterface $package, ?PackageInterface $prevPackage = null): PromiseInterface
    {
        return \React\Promise\resolve(null);
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package): PromiseInterface
    {
        if ($package instanceof ExtraDownloadInterface) {
            $this->executableInstaller->install($package);
        }

        if (!$repo->hasPackage($package)) {
            $repo->addPackage($package);
        }

        return \React\Promise\resolve(null);
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target): PromiseInterface
    {
        return \React\Promise\resolve(null);
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package): PromiseInterface
    {
        return \React\Promise\resolve(null);
    }

    public function getInstallPath(PackageInterface $package): ?string
    {
        if ($package instanceof ExtraDownloadInterface) {
            return $package->getInstallPath();
        }

        return null;
    }
}
