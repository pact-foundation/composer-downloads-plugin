<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoOverwriting;

class LibraryTest extends NoOverwritingTestCase
{
    protected function getFilesFromLibrary(): array
    {
        return parent::getFilesFromLibrary() + [
            'vendor/test/library/library-file.txt' => '29d23ff005bea6ed60f070d25ac499bfc68e1c6ebc40f0cc05ed6d07576c8136',
        ];
    }

    protected function getInfoMessage(): string
    {
        return 'Extra file test/library:no-overwriting has been locally overriden in library-file.txt. To reset it, delete and reinstall.';
    }

    protected static function getLibraryPath(): string
    {
        return realpath(self::getFixturesPath().'/no-overwriting-library');
    }
}
