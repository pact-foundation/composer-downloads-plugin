<?php

namespace LastCall\DownloadsPlugin\Installer;

use Composer\Installer\BinaryInstaller;
use Composer\IO\IOInterface;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;

class ExecutableInstaller implements ExecutableInstallerInterface
{
    public function __construct(private IOInterface $io)
    {
    }

    public function install(ExtraDownloadInterface $extraDownload): void
    {
        foreach ($extraDownload->getExecutablePaths() as $path) {
            if (Platform::isWindows() || Platform::isWindowsSubsystemForLinux()) {
                $proxy = $path.'.bat';
                if (file_exists($proxy)) {
                    $this->io->writeError(sprintf('    Skipped installation of bin %s.bat proxy for package %s: a .bat proxy was already installed', $path, $extraDownload->getName()));
                } else {
                    $caller = BinaryInstaller::determineBinaryCaller($path);
                    file_put_contents($proxy, '@'.$caller.' "%~dp0'.ProcessExecutor::escape(basename($proxy, '.bat')).'" %*');
                }
            } else {
                chmod($path, 0777 & ~umask());
            }
        }
    }
}
