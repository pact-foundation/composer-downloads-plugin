<?php

namespace LastCall\DownloadsPlugin\Parser;

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManager;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Attribute\Validator\ExecutableValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\HashValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\IgnoreValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\PathValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\TypeValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\UrlValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\VariablesValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\VersionValidator;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Factory\ExtraArchiveFactory;
use LastCall\DownloadsPlugin\Factory\ExtraDownloadFactory;

class Parser implements ParserInterface
{
    public function __construct(private IOInterface $io)
    {
    }

    /**
     * @return PackageInterface[]
     */
    public function parse(PackageInterface $package): array
    {
        $extraDownloads = [];
        $extra = $package->getExtra();

        if (empty($extra['downloads'])) {
            return [];
        }

        $defaults = $extra['downloads']['*'] ?? [];
        try {
            foreach ((array) $extra['downloads'] as $id => $attributes) {
                if ('*' === $id) {
                    continue;
                }

                $extraDownloads[] = $this->parseSingle($package, $id, array_merge($defaults, $attributes));
            }

            return $extraDownloads;
        } catch (\Exception $exception) {
            $this->io->writeError(sprintf('    Skipped download extra files for package %s: %s', $package->getName(), $exception->getMessage()));

            return [];
        }
    }

    private function parseSingle(PackageInterface $package, string $id, array $attributes): PackageInterface
    {
        $attributeManager = new AttributeManager($attributes);
        $this->addValidators($attributeManager, $id, $package);
        if ($attributeManager->get(Attribute::TYPE)->isArchive()) {
            $factory = new ExtraArchiveFactory($attributeManager);
        } else {
            $factory = new ExtraDownloadFactory($attributeManager);
        }

        return $factory->create($id, $package);
    }

    private function addValidators(AttributeManagerInterface $attributeManager, string $id, PackageInterface $parent): void
    {
        $validators = [
            new HashValidator($id, $parent),
            new VersionValidator($id, $parent),
            new VariablesValidator($id, $parent, $attributeManager),
            new UrlValidator($id, $parent, $attributeManager),
            new TypeValidator($id, $parent, $attributeManager),
            new PathValidator($id, $parent, $attributeManager),
            new IgnoreValidator($id, $parent, $attributeManager),
            new ExecutableValidator(
                $id,
                $parent,
                $attributeManager,
                new PathValidator($id, $parent, $attributeManager, 'executable[*]')
            ),
        ];
        foreach ($validators as $validator) {
            $attributeManager->addValidator($validator);
        }
    }
}
