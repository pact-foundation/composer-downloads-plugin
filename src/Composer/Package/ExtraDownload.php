<?php

namespace LastCall\DownloadsPlugin\Composer\Package;

use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\InstallPath\InstallPath;
use LastCall\DownloadsPlugin\Composer\Package\InstallPath\InstallPathInterface;
use LastCall\DownloadsPlugin\Composer\Package\Tracking\FileTracking;
use LastCall\DownloadsPlugin\Composer\Package\Tracking\TrackingInterface;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Model\Hash;

class ExtraDownload extends Package implements ExtraDownloadInterface
{
    public const FAKE_VERSION = 'dev-master';

    protected TrackingInterface $tracking;
    private InstallPathInterface $installPath;

    public function __construct(
        PackageInterface $parent,
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
        $this->installPath = new InstallPath($parent);
    }

    public function getTrackingChecksum(): string
    {
        return $this->tracking->getChecksum();
    }

    public function getInstallPath(): string
    {
        return $this->installPath->convertToAbsolute($this->getTargetDir());
    }

    public function getExecutablePaths(): array
    {
        return array_map(fn (string $bin) => $this->installPath->convertToAbsolute($bin), $this->executable);
    }

    public function verifyFile(string $path): bool
    {
        if (null === $this->hash) {
            return true;
        }

        return $this->hash->verifyFile($path);
    }
}
