<?php

namespace LastCall\DownloadsPlugin\Composer\Installer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Enum\PackageType;
use LastCall\DownloadsPlugin\Installer\ExecutableInstallerInterface;
use React\Promise\PromiseInterface;
use Symfony\Component\Finder\Finder;

class FileInstaller extends AbstractInstaller
{
    public const TMP_PREFIX = '.composer-extra-tmp-';

    protected Filesystem $filesystem;

    public function __construct(
        IOInterface $io,
        Composer $composer,
        ?Filesystem $filesystem = null,
        ?ExecutableInstallerInterface $executableInstaller = null,
    ) {
        parent::__construct($io, $composer, $executableInstaller);
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function supports(string $packageType): bool
    {
        return $packageType === PackageType::FILE->value;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package): PromiseInterface
    {
        if (!$package instanceof ExtraDownloadInterface) {
            return \React\Promise\resolve(null);
        }

        $expectedPath = $package->getInstallPath();
        $tmpDir = \dirname($expectedPath).\DIRECTORY_SEPARATOR.uniqid(self::TMP_PREFIX, true);
        $promise = $this->composer->getDownloadManager()->install($package, $tmpDir);

        return $promise->then(function () use ($repo, $package, $expectedPath, $tmpDir) {
            $finder = new Finder();
            foreach ($finder->files()->in($tmpDir) as $file) {
                $this->filesystem->rename($file, $expectedPath);
                break;
            }
            $this->filesystem->remove($tmpDir);

            return parent::install($repo, $package);
        });
    }
}
