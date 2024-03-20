<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\GlobCleaner;
use LastCall\DownloadsPlugin\Subpackage;

abstract class ArchiveHandler extends BaseHandler
{
    protected GlobCleaner $cleaner;

    public function __construct(
        Subpackage $subpackage,
        ?BinariesInstaller $binariesInstaller = null,
        ?Filesystem $filesystem = null,
        ?GlobCleaner $cleaner = null
    ) {
        parent::__construct($subpackage, $binariesInstaller, $filesystem);
        $this->cleaner = $cleaner ?? new GlobCleaner();
    }

    public function getTrackingFile(): string
    {
        $id = $this->subpackage->getSubpackageName();
        $file = basename($id).'-'.md5($id).'.json';

        return
            $this->subpackage->getTargetPath().
            \DIRECTORY_SEPARATOR.self::DOT_DIR.
            \DIRECTORY_SEPARATOR.$file;
    }

    public function getTrackingData(): array
    {
        return ['ignore' => $this->subpackage->getIgnore()] + parent::getTrackingData();
    }

    protected function getChecksumData(): array
    {
        $ignore = array_values($this->subpackage->getIgnore());
        sort($ignore);

        return ['ignore' => $ignore] + parent::getChecksumData();
    }

    protected function handleDownloadedFile(Composer $composer, IOInterface $io, string $file): void
    {
        $this->extract($composer, $this->subpackage->getTargetPath());
        $this->clean();
        $this->installBinaries($composer, $io);
    }

    private function clean(): void
    {
        $this->cleaner->clean($this->subpackage->getTargetPath(), $this->subpackage->getIgnore());
    }
}
