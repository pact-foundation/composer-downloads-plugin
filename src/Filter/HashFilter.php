<?php

namespace LastCall\DownloadsPlugin\Filter;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Model\Hash;

class HashFilter extends BaseFilter
{
    public function __construct(
        string $subpackageName,
        PackageInterface $parent,
    ) {
        parent::__construct($subpackageName, $parent);
    }

    protected function get(array $extraFile): ?Hash
    {
        if (isset($extraFile['hash'])) {
            $hash = $extraFile['hash'];
            if (!\is_array($hash)) {
                $this->throwException('hash', sprintf('must be array, "%s" given', get_debug_type($hash)));
            }

            $algo = $hash['algo'] ?? null;
            if (!\is_string($algo)) {
                $this->throwException('hash > algo', sprintf('must be string, "%s" given', get_debug_type($algo)));
            }

            if (!\in_array($algo, hash_algos(), true)) {
                $this->throwException('hash > algo', 'is not supported');
            }

            $value = $hash['value'] ?? null;
            if (!\is_string($value)) {
                $this->throwException('hash > value', sprintf('must be string, "%s" given', get_debug_type($value)));
            }

            return new Hash($algo, $value);
        }

        return null;
    }
}
