<?php

namespace LastCall\DownloadsPlugin\Composer\Package\Tracking;

use LastCall\DownloadsPlugin\Enum\Type;

class ArchiveTracking extends FileTracking implements TrackingInterface
{
    public function __construct(
        string $id,
        string $url,
        string $path,
        Type $type,
        array $executable,
        private array $ignore,
    ) {
        parent::__construct($id, $url, $path, $type, $executable);
    }

    protected function getChecksumData(): array
    {
        return parent::getChecksumData() + [
            'ignore' => $this->ignore,
        ];
    }
}
