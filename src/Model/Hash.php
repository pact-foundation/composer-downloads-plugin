<?php

namespace LastCall\DownloadsPlugin\Model;

class Hash
{
    public function __construct(public readonly string $algo, public readonly string $value)
    {
    }
}
