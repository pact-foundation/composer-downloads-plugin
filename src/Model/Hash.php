<?php

namespace LastCall\DownloadsPlugin\Model;

class Hash
{
    public function __construct(private string $algo, private string $value)
    {
    }

    public function verifyFile(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        return hash_file($this->algo, $path) === $this->value;
    }
}
