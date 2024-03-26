<?php

namespace LastCall\DownloadsPlugin\Composer\Package;

use Composer\InstalledVersions;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Model\Hash;

class ExtraDownload extends Package implements ExtraDownloadInterface
{
    public const FAKE_VERSION = 'dev-master';

    public function __construct(
        private PackageInterface $parent,
        string $id,
        ?string $version,
        private ?Hash $hash,
        private Type $extraDownloadType,
        private array $executable,
    ) {
        parent::__construct(
            sprintf('%s:%s', $parent->getName(), $id),
            self::FAKE_VERSION,
            $version ?? self::FAKE_VERSION,
        );
        $this->setInstallationSource('dist');
        $this->setDistType($extraDownloadType->toDistType());
        $this->setType($extraDownloadType->toPackageType()->value);
    }

    public function getTrackingChecksum(): string
    {
        return hash('sha256', serialize($this->getTrackingChecksumData()));
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

    protected function getTrackingChecksumData(): array
    {
        return [
            'id' => $this->getName(),
            'url' => $this->getDistUrl(),
            'path' => $this->getTargetDir(),
            'type' => $this->extraDownloadType,
            'executable' => $this->executable,
        ];
    }

    private function getParentPath(): string
    {
        return InstalledVersions::getInstallPath($this->parent->getName());
    }
}
