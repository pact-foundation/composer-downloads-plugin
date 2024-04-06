<?php

namespace LastCall\DownloadsPlugin\Composer\Package\Tracking;

use LastCall\DownloadsPlugin\Enum\Type;

class FileTracking implements TrackingInterface
{
    public function __construct(
        private string $id,
        private string $url,
        private string $path,
        private Type $type,
        private array $executable,
    ) {
    }

    public function getChecksum(): string
    {
        return hash('sha256', serialize($this->getChecksumData()));
    }

    protected function getChecksumData(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'path' => $this->path,
            'type' => $this->type,
            'executable' => $this->executable,
        ];
    }
}
