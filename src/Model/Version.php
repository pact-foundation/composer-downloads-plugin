<?php

namespace LastCall\DownloadsPlugin\Model;

class Version
{
    public function __construct(public readonly string $version, public readonly string $prettyVersion)
    {
    }
}
