<?php

namespace LastCall\DownloadsPlugin\Composer\Package\InstallPath;

interface InstallPathInterface
{
    public function convertToAbsolute(string $relative): string;
}
