<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Hash;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class ValidDownloadedFileTest extends CommandTestCase
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
                    'value' => '03df6683864fe411969b403b772fbf97',
                ],
            ],
            'archive' => [
                'url' => 'http://localhost:8000/archive/doc/v1.2.3/doc.zip',
                'path' => 'files/archive',
                'hash' => [
                    'algo' => 'sha256',
                    'value' => 'bd55e83aafb4405901e6f6ce516aca807661dde6ffde451d8e3fea97e39a8be5',
                ],
            ],
            'gzip' => [
                'url' => 'http://localhost:8000/archive/empty.xml.gz',
                'path' => 'files/gzip',
                'hash' => [
                    'algo' => 'sha512',
                    'value' => '8ef8a07339dbc2fec220f14e49af5c606ce0667fd2528ea6c8da9bd757ee6cfec2d94823205372c972b4ebd52d86cde66d549a5a4fe7a73de227f6ae1a4b0a82',
                ],
            ],
        ];

        return $output;
    }

    protected function getFiles(): array
    {
        return [
            'files/file' => '66ef5d9bd7854d96e0c3b05e8c169a5fbd398ece5299032c132387edb87cf491',
            'files/archive/empty.doc' => '60b5e45db3b51c38a5b762e771ee2f19692f52186c42c3930d56bbdf04d21f4e',
            'files/archive/empty.docx' => '61cdb4b8b9067ab1f4eaa5ba782007c81bdd04283a228b5076aeeb4c9362020b',
            'files/gzip' => '4be690ad5983b2a40f640481fdb27dcc43ac162e14fa9aab2ff45775521d9213',
        ];
    }

    protected function getExecutableFiles(): array
    {
        return [];
    }

    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
    }
}
