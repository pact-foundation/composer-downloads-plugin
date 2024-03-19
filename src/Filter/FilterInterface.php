<?php

namespace LastCall\DownloadsPlugin\Filter;

interface FilterInterface
{
    public function filter(array $extraFile): mixed;
}
