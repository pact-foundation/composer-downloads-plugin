<?php

namespace LastCall\DownloadsPlugin\Composer\Repository;

use Composer\Installer\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\InvalidRepositoryException;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;

class ExtraDownloadsRepository implements InstalledRepositoryInterface
{
    private array $extraDownloads;

    public function __construct(private JsonFile $file)
    {
        $this->initialize();
    }

    public function addPackage(PackageInterface $package): void
    {
        if ($package instanceof ExtraDownloadInterface) {
            $this->extraDownloads[$package->getName()] = $package->getTrackingChecksum();
        }
    }

    public function removePackage(PackageInterface $package): void
    {
        if ($package instanceof ExtraDownloadInterface) {
            unset($this->extraDownloads[$package->getName()]);
        }
    }

    public function hasPackage(PackageInterface $package): bool
    {
        if (!$package instanceof ExtraDownloadInterface) {
            return false;
        }
        $name = $package->getName();
        if (!isset($this->extraDownloads[$name])) {
            return false;
        }

        return $this->extraDownloads[$name] === $package->getTrackingChecksum();
    }

    protected function initialize(): void
    {
        $this->extraDownloads = [];

        if (!$this->file->exists()) {
            return;
        }

        try {
            $packages = $this->file->read();

            if (!\is_array($packages)) {
                throw new \UnexpectedValueException('Could not parse package list from the repository');
            }
        } catch (\Exception $e) {
            throw new InvalidRepositoryException('Invalid repository data in '.$this->file->getPath().', packages could not be loaded: ['.$e::class.'] '.$e->getMessage());
        }

        foreach ($packages as $name => $trackingChecksum) {
            $this->extraDownloads[$name] = $trackingChecksum;
        }
    }

    public function reload(): void
    {
        $this->initialize();
    }

    public function isFresh(): bool
    {
        return !$this->file->exists();
    }

    public function write(bool $devMode, InstallationManager $installationManager): void
    {
        $this->file->write($this->extraDownloads);
    }

    public function getCanonicalPackages(): array
    {
        return [];
    }

    public function getDevPackageNames(): array
    {
        return [];
    }

    public function setDevPackageNames(array $devPackageNames): void
    {
    }

    public function count(): int
    {
        return 0;
    }

    public function getRepoName(): string
    {
        return 'installed extra downloads';
    }

    public function getDevMode(): ?bool
    {
        return null;
    }

    public function findPackage(string $name, $constraint): ?PackageInterface
    {
        return null;
    }

    public function findPackages(string $name, $constraint = null): array
    {
        return [];
    }

    public function getPackages(): array
    {
        return [];
    }

    public function getProviders(string $packageName): array
    {
        return [];
    }

    public function loadPackages(array $packageNameMap, array $acceptableStabilities, array $stabilityFlags, array $alreadyLoaded = []): array
    {
        return [];
    }

    public function search(string $query, int $mode = 0, ?string $type = null): array
    {
        return [];
    }
}
