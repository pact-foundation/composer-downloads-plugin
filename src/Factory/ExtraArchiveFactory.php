<?php

namespace LastCall\DownloadsPlugin\Factory;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraArchive;
use LastCall\DownloadsPlugin\Enum\Attribute;

class ExtraArchiveFactory extends ExtraDownloadFactory
{
    public function create(string $id, PackageInterface $parent): PackageInterface
    {
        return new ExtraArchive(
            $parent,
            $id,
            $this->attributeManager->get(Attribute::VERSION),
            $this->attributeManager->get(Attribute::HASH),
            $this->attributeManager->get(Attribute::TYPE),
            $this->attributeManager->get(Attribute::EXECUTABLE),
            $this->attributeManager->get(Attribute::URL),
            $this->attributeManager->get(Attribute::PATH),
            $this->attributeManager->get(Attribute::IGNORE),
        );
    }
}
