<?php

namespace LastCall\DownloadsPlugin\Enum;

enum Attribute: string
{
    case VERSION = 'version';
    case HASH = 'hash';
    case URL = 'url';
    case PATH = 'path';
    case TYPE = 'type';
    case EXECUTABLE = 'executable';
    case IGNORE = 'ignore';
    case VARIABLES = 'variables';
}
