<?php

namespace LastCall\DownloadsPlugin\Handler;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use LastCall\DownloadsPlugin\Composer\Repository\ExtraDownloadsRepository;
use LastCall\DownloadsPlugin\Parser\Parser;
use LastCall\DownloadsPlugin\Parser\ParserInterface;

class PackageHandler implements PackageHandlerInterface
{
    private ParserInterface $parser;
    private InstalledRepositoryInterface $repository;

    public function __construct(
        private Composer $composer,
        private IOInterface $io,
        ?ParserInterface $parser = null,
        ?InstalledRepositoryInterface $repository = null,
    ) {
        $this->parser = $parser ?? new Parser($this->io);
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $this->repository = $repository ?? new ExtraDownloadsRepository(new JsonFile($vendorDir.'/composer/installed-extra-downloads.json', null, $this->io));
    }

    public function handle(PackageInterface $package): void
    {
        $installationManager = $this->composer->getInstallationManager();
        $extraDownloads = array_filter(
            $this->parser->parse($package),
            fn (PackageInterface $extraDownload) => !$installationManager->isPackageInstalled($this->repository, $extraDownload)
        );
        if (empty($extraDownloads)) {
            return;
        }
        $operations = array_map(
            fn (PackageInterface $extraDownload) => new InstallOperation($extraDownload),
            array_values($extraDownloads)
        );
        $installationManager->execute($this->repository, $operations, false, false);
        if (!$package instanceof RootPackageInterface) {
            $installationManager->ensureBinariesPresence($package);
        }
    }
}
