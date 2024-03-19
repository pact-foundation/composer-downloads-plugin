<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Hash;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InvalidDownloadedFileTest extends CommandTestCase
{
    protected static function getComposerJson(): array
    {
        $output = parent::getComposerJson();
        unset($output['require']['test/library']);
        $output['extra']['downloads'] = [
            'file' => [
                'type' => 'phar',
                'url' => 'http://localhost:8000/phar/hello.phar',
                'path' => 'files/file',
                'hash' => [
                    'algo' => 'md5',
                    'value' => 'not-correct',
                ],
            ],
            'archive' => [
                'url' => 'http://localhost:8000/archive/doc/v1.2.3/doc.zip',
                'path' => 'files/archive',
                'hash' => [
                    'algo' => 'sha256',
                    'value' => 'not-correct',
                ],
            ],
            'gzip' => [
                'url' => 'http://localhost:8000/archive/empty.xml.gz',
                'path' => 'files/gzip',
                'hash' => [
                    'algo' => 'sha512',
                    'value' => 'not-correct',
                ],
            ],
        ];

        return $output;
    }

    protected function getFiles(): array
    {
        return [
            'files/file' => false,
            'files/archive/empty.doc' => false,
            'files/archive/empty.docx' => false,
            'files/gzip' => false,
        ];
    }

    protected function getExecutableFiles(): array
    {
        return [];
    }

    public function testDownload(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->runComposerCommandAndAssert(['install']);
    }
}
