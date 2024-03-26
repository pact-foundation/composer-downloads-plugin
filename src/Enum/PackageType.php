<?php

namespace LastCall\DownloadsPlugin\Enum;

enum PackageType: string
{
    private const TYPE_PREFIX = 'extra-download:';

    case ARCHIVE = self::TYPE_PREFIX.'archive';
    case GZIP = self::TYPE_PREFIX.'gzip';
    case FILE = self::TYPE_PREFIX.'file';
}
