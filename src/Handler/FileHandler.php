<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;

class FileHandler extends BaseHandler
{
    public const TMP_PREFIX = '.composer-extra-tmp-';

    public function getTrackingFile(): string
    {
        $id = $this->subpackage->getSubpackageName();
        $file = $id.'-'.md5($id).'.json';

        return
            \dirname($this->subpackage->getTargetPath()).
            \DIRECTORY_SEPARATOR.self::DOT_DIR.
            \DIRECTORY_SEPARATOR.$file;
    }

    public function install(Composer $composer, IOInterface $io): void
    {
        // We want to take advantage of the cache in composer's downloader, but it
        // doesn't put the file the spot we want, so we shuffle a bit.
        $file = $this->download($composer);
        if ($this->validateDownloadedFile($file)) {
            $this->move($file);
            $this->installBinaries($composer, $io);
        } else {
            $this->handleInvalidDownloadedFile($file);
        }
    }
}
