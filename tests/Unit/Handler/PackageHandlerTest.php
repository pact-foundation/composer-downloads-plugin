<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackage;
use Composer\Repository\InstalledRepositoryInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Handler\PackageHandler;
use LastCall\DownloadsPlugin\Handler\PackageHandlerInterface;
use LastCall\DownloadsPlugin\Parser\ParserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PackageHandlerTest extends TestCase
{
    private Composer|MockObject $composer;
    private IOInterface|MockObject $io;
    protected ParserInterface|MockObject $parser;
    protected InstalledRepositoryInterface|MockObject $repository;
    private InstallationManager|MockObject $installationManager;
    private PackageHandlerInterface $handler;

    protected function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->parser = $this->createMock(ParserInterface::class);
        $this->repository = $this->createMock(InstalledRepositoryInterface::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
        $this->handler = new PackageHandler(
            $this->composer,
            $this->io,
            $this->parser,
            $this->repository
        );
    }

    public function testHandleEmptyExtraDownloads(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $this->composer
            ->expects($this->once())
            ->method('getInstallationManager')
            ->willReturn($this->installationManager);
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($package)
            ->willReturn([]);
        $this->installationManager
            ->expects($this->never())
            ->method('isPackageInstalled');
        $this->installationManager
            ->expects($this->never())
            ->method('execute');
        $this->installationManager
            ->expects($this->never())
            ->method('ensureBinariesPresence');
        $this->handler->handle($package);
    }

    public function testHandleAllExtraDownloadsInstalled(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $this->composer
            ->expects($this->once())
            ->method('getInstallationManager')
            ->willReturn($this->installationManager);
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($package)
            ->willReturn([
                $extraDownload1 = $this->createMock(ExtraDownloadInterface::class),
                $extraDownload2 = $this->createMock(ExtraDownloadInterface::class),
                $extraDownload3 = $this->createMock(ExtraDownloadInterface::class),
            ]);
        $this->installationManager
            ->expects($this->exactly(3))
            ->method('isPackageInstalled')
            ->withConsecutive(
                [$this->repository, $extraDownload1],
                [$this->repository, $extraDownload2],
                [$this->repository, $extraDownload3]
            )
            ->willReturn(true);
        $this->installationManager
            ->expects($this->never())
            ->method('execute');
        $this->installationManager
            ->expects($this->never())
            ->method('ensureBinariesPresence');
        $this->handler->handle($package);
    }

    public function getHandleTests(): array
    {
        return [
            [new Package('vendor/library-name', '1.0.0', 'v1.0.0'), true],
            [new RootPackage('my-organization/my-project', '1.2.3', 'v1.2.3'), false],
        ];
    }

    /**
     * @dataProvider getHandleTests
     */
    public function testHandle(PackageInterface $package, bool $ensureBinariesPresence): void
    {
        $this->composer
            ->expects($this->once())
            ->method('getInstallationManager')
            ->willReturn($this->installationManager);
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($package)
            ->willReturn([
                $extraDownload1 = $this->createMock(ExtraDownloadInterface::class),
                $extraDownload2 = $this->createMock(ExtraDownloadInterface::class),
                $extraDownload3 = $this->createMock(ExtraDownloadInterface::class),
            ]);
        $this->installationManager
            ->expects($this->exactly(3))
            ->method('isPackageInstalled')
            ->withConsecutive(
                [$this->repository, $extraDownload1],
                [$this->repository, $extraDownload2],
                [$this->repository, $extraDownload3]
            )
            ->willReturnOnConsecutiveCalls(false, true, false);
        $this->installationManager
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->repository,
                $this->callback(function (array $operations) use ($extraDownload1, $extraDownload3) {
                    $this->assertCount(2, $operations);
                    $this->assertInstanceOf(InstallOperation::class, $operations[0]);
                    $this->assertSame($extraDownload1, $operations[0]->getPackage());
                    $this->assertInstanceOf(InstallOperation::class, $operations[1]);
                    $this->assertSame($extraDownload3, $operations[1]->getPackage());

                    return true;
                }),
                false,
                false,
            );
        $this->installationManager
            ->expects($this->exactly($ensureBinariesPresence))
            ->method('ensureBinariesPresence')
            ->with($package);
        $this->handler->handle($package);
    }
}
