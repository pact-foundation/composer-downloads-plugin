<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;

class GzipHandler extends FileHandler
{
    public function install(Composer $composer, IOInterface $io): void
    {
        $tmpDir = \dirname($this->subpackage->getTargetPath()).\DIRECTORY_SEPARATOR.uniqid(self::TMP_PREFIX, true);
        $targetName = pathinfo($this->subpackage->getDistUrl(), \PATHINFO_FILENAME);
        $filePath = $tmpDir.\DIRECTORY_SEPARATOR.$targetName;

        $file = $this->download($composer);
        if ($this->validateDownloadedFile($file)) {
            $this->extract($composer, $tmpDir);
            $this->move($filePath);
            $this->installBinaries($composer, $io);
            $this->remove($tmpDir);
        } else {
            $this->handleInvalidDownloadedFile($file);
        }
    }
}
