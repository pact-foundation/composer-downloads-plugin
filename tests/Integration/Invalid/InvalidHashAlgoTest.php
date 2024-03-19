<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidHashAlgoTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-hash-algo';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/hash',
            'url' => 'http://localhost:8000/file/ipsum',
            'hash' => [
                'algo' => 'invalid',
            ],
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "hash > algo" of extra file "invalid-hash-algo" defined in package "test/project" is not supported.';
    }
}
