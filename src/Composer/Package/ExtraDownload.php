<?php

namespace LastCall\DownloadsPlugin\Composer\Package;

use Composer\InstalledVersions;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\Tracking\FileTracking;
use LastCall\DownloadsPlugin\Composer\Package\Tracking\TrackingInterface;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Model\Hash;

class ExtraDownload extends Package implements ExtraDownloadInterface
{
    public const FAKE_VERSION = 'dev-master';

    protected TrackingInterface $tracking;

    public function __construct(
        private PackageInterface $parent,
        string $id,
        ?string $version,
        private ?Hash $hash,
        Type $type,
        private array $executable,
        string $url,
        string $path,
    ) {
        parent::__construct(
            sprintf('%s:%s', $parent->getName(), $id),
            self::FAKE_VERSION,
            $version ?? self::FAKE_VERSION,
        );
        $this->setInstallationSource('dist');
        $this->setDistType($type->toDistType());
        $this->setType($type->toPackageType()->value);
        $this->setDistUrl($url);
        $this->setTargetDir($path);
        $this->tracking = new FileTracking($this->getName(), $url, $path, $type, $executable);
    }

    public function getTrackingChecksum(): string
    {
        return $this->tracking->getChecksum();
    }

    public function getInstallPath(): string
    {
        return $this->getParentPath().\DIRECTORY_SEPARATOR.$this->getTargetDir();
    }

    public function getExecutablePaths(): array
    {
        return array_map(fn (string $bin) => $this->getParentPath().\DIRECTORY_SEPARATOR.$bin, $this->executable);
    }

    public function verifyFile(string $path): bool
    {
        if (null === $this->hash) {
            return true;
        }

        return $this->hash->verifyFile($path);
    }

    private function getParentPath(): string
    {
        return InstalledVersions::getInstallPath($this->parent->getName());
    }
}
