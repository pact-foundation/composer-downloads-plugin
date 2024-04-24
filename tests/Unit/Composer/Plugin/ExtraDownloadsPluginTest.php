<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Plugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\InstallationManager;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use LastCall\DownloadsPlugin\Composer\Installer\ArchiveInstaller;
use LastCall\DownloadsPlugin\Composer\Installer\FileInstaller;
use LastCall\DownloadsPlugin\Composer\Plugin\ExtraDownloadsPlugin;
use LastCall\DownloadsPlugin\Exception\OutOfRangeException;
use LastCall\DownloadsPlugin\Handler\PackageHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtraDownloadsPluginTest extends TestCase
{
    private PackageHandlerInterface|MockObject $handler;
    private ExtraDownloadsPlugin $plugin;
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    private InstallationManager|MockObject $installationManager;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(PackageHandlerInterface::class);
        $this->plugin = new ExtraDownloadsPlugin($this->handler);
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([
            PackageEvents::POST_PACKAGE_INSTALL => ['handlePackageEvent', 10],
            PackageEvents::POST_PACKAGE_UPDATE => ['handlePackageEvent', 10],
            ScriptEvents::POST_INSTALL_CMD => ['handleScriptEvent', 10],
            ScriptEvents::POST_UPDATE_CMD => ['handleScriptEvent', 10],
        ], ExtraDownloadsPlugin::getSubscribedEvents());
    }

    public function testActivate(): void
    {
        $this->composer
            ->expects($this->once())
            ->method('getInstallationManager')
            ->willReturn($this->installationManager);
        $this->installationManager
            ->expects($this->exactly(2))
            ->method('addInstaller')
            ->withConsecutive(
                [$this->isInstanceOf(ArchiveInstaller::class)],
                [$this->isInstanceOf(FileInstaller::class)],
            );
        $this->plugin->activate($this->composer, $this->io);
    }

    public function testDeactivate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->deactivate($this->composer, $this->io);
    }

    public function testUninstall(): void
    {
        $this->expectNotToPerformAssertions();
        $this->plugin->uninstall($this->composer, $this->io);
    }

    public function testHandleScriptEvent(): void
    {
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $this->composer->expects($this->once())->method('getPackage')->willReturn($rootPackage);
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $this->composer->expects($this->once())->method('getRepositoryManager')->willReturn($repositoryManager);
        $localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $repositoryManager->expects($this->once())->method('getLocalRepository')->willReturn($localRepository);
        $packages = [
            $this->createMock(PackageInterface::class),
            $this->createMock(PackageInterface::class),
            $this->createMock(PackageInterface::class),
        ];
        $localRepository->expects($this->once())->method('getCanonicalPackages')->willReturn($packages);
        $this->handler
            ->expects($this->exactly(\count($packages) + 1))
            ->method('handle')
            ->withConsecutive(
                [$rootPackage],
                ...array_map(fn (PackageInterface $package) => [$package], $packages),
            );
        $event = new Event('name', $this->composer, $this->io);
        $this->plugin->handleScriptEvent($event);
    }

    public function testHandleInstallPackageEvent(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($package);
        $event = new PackageEvent(
            'install',
            $this->composer,
            $this->io,
            false,
            $this->createMock(RepositoryInterface::class),
            [],
            new InstallOperation($package)
        );
        $this->plugin->handlePackageEvent($event);
    }

    public function testHandleUpdatePackageEvent(): void
    {
        $initial = $this->createMock(PackageInterface::class);
        $target = $this->createMock(PackageInterface::class);
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($target);
        $event = new PackageEvent(
            'update',
            $this->composer,
            $this->io,
            false,
            $this->createMock(RepositoryInterface::class),
            [],
            new UpdateOperation($initial, $target)
        );
        $this->plugin->handlePackageEvent($event);
    }

    public function testHandleUninstallPackageEvent(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Operation uninstall not supported');
        $package = $this->createMock(PackageInterface::class);
        $event = new PackageEvent(
            'uninstall',
            $this->composer,
            $this->io,
            false,
            $this->createMock(RepositoryInterface::class),
            [],
            new UninstallOperation($package)
        );
        $this->plugin->handlePackageEvent($event);
    }
}
