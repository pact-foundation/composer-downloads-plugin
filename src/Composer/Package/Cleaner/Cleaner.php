<?php

namespace LastCall\DownloadsPlugin\Composer\Package\Cleaner;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Gitignore;

class Cleaner implements CleanerInterface
{
    public function __construct(private array $ignore)
    {
    }

    public function clean(string $dir): void
    {
        if (empty($this->ignore)) {
            return;
        }

        $finder = new Finder();
        $finder->in($dir)->path(Gitignore::toRegex(implode(\PHP_EOL, $this->ignore)));

        $dirs = [];
        foreach ($finder as $item) {
            if ($item->isDir()) {
                $dirs[] = $item->getPathname();
            } else {
                unlink($item->getPathname());
            }
        }
        foreach ($dirs as $dir) {
            @rmdir($dir);
        }
    }
}
