<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoOverwriting;

class ProjectTest extends NoOverwritingTestCase
{
    protected static function initTestProject(): void
    {
        parent::initTestProject();
        file_put_contents(self::getPathToTestDir('project-file.txt'), 'original file from project');
    }

    protected static function getComposerJson(): array
    {
        $json = parent::getComposerJson();
        $json['extra']['downloads']['no-overwriting'] = [
            'type' => 'file',
            'url' => 'http://localhost:8000/file/ipsum',
            'path' => 'project-file.txt',
        ];

        return $json;
    }

    protected function getFilesFromProject(): array
    {
        return parent::getFilesFromProject() + [
            'project-file.txt' => '35cd36335935e2e6f5390dea5870529a9f8276056a7d87b957790d089772121e',
        ];
    }

    protected function shouldExistBeforeCommand(string $file): bool
    {
        return 'project-file.txt' === $file;
    }

    protected function getInfoMessage(): string
    {
        return 'Extra file test/project:no-overwriting has been locally overriden in project-file.txt. To reset it, delete and reinstall.';
    }
}
