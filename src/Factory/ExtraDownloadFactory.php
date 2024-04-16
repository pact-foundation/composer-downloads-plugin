<?php

namespace LastCall\DownloadsPlugin\Factory;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownload;
use LastCall\DownloadsPlugin\Enum\Attribute;

class ExtraDownloadFactory implements ExtraDownloadFactoryInterface
{
    public function __construct(
        protected AttributeManagerInterface $attributeManager,
    ) {
    }

    public function create(string $id, PackageInterface $parent): PackageInterface
    {
        return new ExtraDownload(
            $parent,
            $id,
            $this->attributeManager->get(Attribute::VERSION),
            $this->attributeManager->get(Attribute::HASH),
            $this->attributeManager->get(Attribute::TYPE),
            $this->attributeManager->get(Attribute::EXECUTABLE),
            $this->attributeManager->get(Attribute::URL),
            $this->attributeManager->get(Attribute::PATH),
        );
    }
}
