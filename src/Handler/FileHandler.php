<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;

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

    protected function handleDownloadedFile(Composer $composer, string $file): void
    {
        $this->move($file);
    }
}
