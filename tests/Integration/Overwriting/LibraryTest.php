<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoOverwriting;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class LibraryTest extends CommandTestCase
{
    private static bool $isInstalled = false;

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
        static::$isInstalled = true;
        $this->runComposerCommandAndAssert(['update', 'test/library']);
    }

    protected function getFilesFromLibrary(): array
    {
        $files = parent::getFilesFromLibrary();
        if (static::$isInstalled) {
            return [
                'vendor/test/library/files/mix/bin/hello-python' => false,
                'vendor/test/library/files/mix/bin/hello-python.bat' => false,
                'vendor/test/library/files/mix/doc/empty.epub' => false,
                'vendor/test/library/files/mix/img/empty.svg' => false,
                'vendor/test/library/files/mix' => \PHP_OS_FAMILY === 'Windows'
                    ? '77559b8e3cf8082554f5cb314729363017de998b63f0ab9cb751246c167d7bdd'
                    : '77bdfb1d37ee5a5e6d08d0bd8f2d4abfde6b673422364ba9ad432deb2d9c6e4d', // New line chars are replaced in Windows
            ] + $files;
        } else {
            return $files;
        }
    }

    protected function getExecutableFilesFromLibrary(): array
    {
        $result = parent::getExecutableFilesFromLibrary();
        if (static::$isInstalled) {
            unset($result['vendor/test/library/files/mix/bin/hello-python']);
        }

        return $result;
    }

    protected function getMissingExecutableFiles(): array
    {
        if (static::$isInstalled) {
            return ['vendor/test/library/files/mix/bin/hello-python'];
        } else {
            return [];
        }
    }

    protected static function getLibraryPath(): string
    {
        if (static::$isInstalled) {
            return realpath(self::getFixturesPath().'/overwriting-library');
        } else {
            return parent::getLibraryPath();
        }
    }

    protected function shouldExistBeforeCommand(string $file): bool
    {
        return static::$isInstalled;
    }
}
