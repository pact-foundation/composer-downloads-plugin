<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidHashTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-hash';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/hash',
            'url' => 'http://localhost:8000/file/ipsum',
            'hash' => 'e86186d2b34630a05ead6cff9d29c5c3',
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "hash" of extra file "invalid-hash" defined in package "test/project" must be array, "string" given.';
    }
}
