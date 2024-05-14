<?php

namespace LastCall\DownloadsPlugin\Helper;

class MuslDetector
{
    public function __invoke(): bool
    {
        exec('ldd /bin/sh 2>&1', $output, $returnCode);
        if (0 !== $returnCode) {
            return false;
        }
        $ldd = trim(implode("\n", $output));

        return str_contains($ldd, 'musl');
    }
}
