<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoOverwriting;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class ProjectTest extends CommandTestCase
{
    private static bool $isInstalled = false;

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
        static::$isInstalled = true;
        $this->runComposerCommandAndAssert(['update']);
    }

    protected static function getComposerJson(): array
    {
        $json = parent::getComposerJson();
        if (static::$isInstalled) {
            $json['extra']['downloads']['file'] = [
                'url' => 'http://localhost:8000/archive/text.tar',
                'path' => 'files/file/ipsum',
            ];
        }

        return $json;
    }

    protected function getFilesFromProject(): array
    {
        $files = parent::getFilesFromProject();
        if (static::$isInstalled) {
            return [
                'files/file/ipsum' => false,
                'files/file/ipsum/empty.csv' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'files/file/ipsum/empty.json' => 'ca3d163bab055381827226140568f3bef7eaac187cebd76878e0b63e9e442356',
                'files/file/ipsum/empty.txt' => 'b42f2099187886def637d6aa840022266e05cb6c987a9394e708e23cd505eb46',
            ] + $files;
        } else {
            return $files;
        }
    }
}
