<?php
/**
 * Created by PhpStorm.
 * User: totten
 * Date: 8/21/19
 * Time: 6:31 PM.
 */

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Exception\InvalidDownloadedFileException;
use LastCall\DownloadsPlugin\Subpackage;

abstract class BaseHandler implements HandlerInterface
{
    public const DOT_DIR = '.composer-downloads';

    private BinariesInstaller $binariesInstaller;

    protected Filesystem $filesystem;

    public function __construct(
        protected Subpackage $subpackage,
        ?BinariesInstaller $binariesInstaller = null,
        ?Filesystem $filesystem = null
    ) {
        $this->binariesInstaller = $binariesInstaller ?? new BinariesInstaller();
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function getSubpackage(): Subpackage
    {
        return $this->subpackage;
    }

    public function getTrackingData(): array
    {
        return [
            'name' => $this->subpackage->getName(),
            'url' => $this->subpackage->getDistUrl(),
            'checksum' => $this->getChecksum(),
        ];
    }

    /**
     * @return string A unique identifier for this configuration of this asset.
     *                If the identifier changes, that implies that the asset should be
     *                replaced/redownloaded.
     */
    public function getChecksum(): string
    {
        return hash('sha256', serialize($this->getChecksumData()));
    }

    protected function getChecksumData(): array
    {
        return [
            'class' => static::class,
            'id' => $this->subpackage->getSubpackageName(),
            'url' => $this->subpackage->getDistUrl(),
            'path' => $this->subpackage->getTargetDir(),
        ];
    }

    protected function installBinaries(Composer $composer, IOInterface $io): void
    {
        $this->binariesInstaller->install($this->subpackage, $io);
    }

    /**
     * Download file to temporary place ("vendor/composer/tmp-[random-file-name]"), return downloaded file's path.
     */
    protected function download(Composer $composer): string
    {
        $downloadManager = $composer->getDownloadManager();

        $file = '';
        $promise = $downloadManager->download($this->subpackage, \dirname($this->subpackage->getTargetPath()));
        $promise->then(static function ($res) use (&$file) {
            $file = $res;

            return \React\Promise\resolve($res);
        });
        $composer->getLoop()->wait([$promise]);

        return $file;
    }

    protected function validateDownloadedFile(string $filePath): bool
    {
        $hash = $this->subpackage->getHash();

        if (!$hash) {
            return true;
        }

        return hash_file($hash->algo, $filePath) === $hash->value;
    }

    protected function remove(string $file): void
    {
        $this->filesystem->remove($file);
    }

    protected function move(string $file): void
    {
        $this->filesystem->rename($file, $this->subpackage->getTargetPath());
    }

    /**
     * Extract downloaded file to new path.
     */
    protected function extract(Composer $composer, string $targetPath): void
    {
        $downloadManager = $composer->getDownloadManager();

        $promise = $downloadManager->install($this->subpackage, $targetPath);
        $composer->getLoop()->wait([$promise]);
    }

    protected function handleInvalidDownloadedFile(string $file): void
    {
        $this->remove($file);
        throw new InvalidDownloadedFileException(sprintf('Extra file "%s" does not match hash value defined in "%s".', $this->subpackage->getDistUrl(), $this->subpackage->getSubpackageName()));
    }
}
