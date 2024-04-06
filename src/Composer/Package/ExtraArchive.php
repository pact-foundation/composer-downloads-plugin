<?php

namespace LastCall\DownloadsPlugin\Composer\Package;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\Cleaner\Cleaner;
use LastCall\DownloadsPlugin\Composer\Package\Cleaner\CleanerInterface;
use LastCall\DownloadsPlugin\Composer\Package\Tracking\ArchiveTracking;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Model\Hash;

class ExtraArchive extends ExtraDownload implements ExtraArchiveInterface
{
    private CleanerInterface $cleaner;

    public function __construct(
        PackageInterface $parent,
        string $id,
        ?string $version,
        ?Hash $hash,
        Type $type,
        array $executable,
        string $url,
        string $path,
        private array $ignore,
    ) {
        parent::__construct(
            $parent,
            $id,
            $version,
            $hash,
            $type,
            $executable,
            $url,
            $path,
        );
        $this->tracking = new ArchiveTracking($this->getName(), $url, $path, $type, $executable, $ignore);
        $this->cleaner = new Cleaner($ignore);
    }

    public function clean(): void
    {
        $this->cleaner->clean($this->getInstallPath());
    }
}
