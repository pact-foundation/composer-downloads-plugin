<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;

class GzipHandler extends FileHandler
{
    protected function handleDownloadedFile(Composer $composer, IOInterface $io, string $file): void
    {
        $tmpDir = \dirname($this->subpackage->getTargetPath()).\DIRECTORY_SEPARATOR.uniqid(self::TMP_PREFIX, true);
        $targetName = pathinfo($this->subpackage->getDistUrl(), \PATHINFO_FILENAME);

        $this->extract($composer, $tmpDir);
        $this->move($tmpDir.\DIRECTORY_SEPARATOR.$targetName);
        $this->installBinaries($composer, $io);
        $this->remove($tmpDir);
    }
}
