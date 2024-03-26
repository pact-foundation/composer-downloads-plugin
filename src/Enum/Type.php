<?php

namespace LastCall\DownloadsPlugin\Enum;

enum Type: string
{
    case ZIP = 'zip';
    case RAR = 'rar';
    case TAR = 'tar';
    case XZ = 'xz';
    case FILE = 'file';
    case PHAR = 'phar';
    case GZIP = 'gzip';

    public function isArchive(): bool
    {
        return match ($this) {
            self::ZIP => true,
            self::RAR => true,
            self::TAR => true,
            self::XZ => true,
            default => false,
        };
    }

    public static function fromExtension(string $extension): self
    {
        return match ($extension) {
            'zip' => self::ZIP,
            'rar' => self::RAR,
            'tgz', 'tar' => self::TAR,
            'gz' => self::GZIP,
            'phar' => self::PHAR,
            default => self::FILE,
        };
    }

    public function toDistType(): string
    {
        return self::PHAR === $this ? 'file' : $this->value;
    }

    public function toPackageType(): PackageType
    {
        if ($this->isArchive()) {
            return PackageType::ARCHIVE;
        }
        if (self::GZIP === $this) {
            return PackageType::GZIP;
        }

        return PackageType::FILE;
    }
}
