<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Invalid;

class InvalidHashValueTest extends InstallInvalidExtraDownloadsTest
{
    protected static function getId(): string
    {
        return 'invalid-hash-value';
    }

    protected static function getExtraFile(): array
    {
        return [
            'path' => 'files/invalid/hash',
            'url' => 'http://localhost:8000/file/ipsum',
            'hash' => [
                'algo' => 'md5',
                'value' => [
                    'key' => 'value',
                ],
            ],
        ];
    }

    protected static function getErrorMessage(): string
    {
        return 'Skipped download extra files for package test/project: Attribute "hash > value" of extra file "invalid-hash-value" defined in package "test/project" must be string, "array" given.';
    }
}
