<?php

namespace LastCall\DownloadsPlugin\Composer\Plugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use LastCall\DownloadsPlugin\Composer\Installer\ArchiveInstaller;
use LastCall\DownloadsPlugin\Composer\Installer\FileInstaller;
use LastCall\DownloadsPlugin\Exception\OutOfRangeException;
use LastCall\DownloadsPlugin\Handler\PackageHandler;
use LastCall\DownloadsPlugin\Handler\PackageHandlerInterface;

class ExtraDownloadsPlugin implements PluginInterface, EventSubscriberInterface
{
    private const EVENT_PRIORITY = 10;

    public function __construct(private ?PackageHandlerInterface $handler = null)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => ['handlePackageEvent', self::EVENT_PRIORITY],
            PackageEvents::POST_PACKAGE_UPDATE => ['handlePackageEvent', self::EVENT_PRIORITY],
            ScriptEvents::POST_INSTALL_CMD => ['handleScriptEvent', self::EVENT_PRIORITY],
            ScriptEvents::POST_UPDATE_CMD => ['handleScriptEvent', self::EVENT_PRIORITY],
        ];
    }

    public function handleScriptEvent(Event $event): void
    {
        $rootPackage = $event->getComposer()->getPackage();
        $this->getHandler($event->getComposer(), $event->getIO())->handle($rootPackage);

        // Ensure that any other packages are properly reconciled.
        $localRepo = $event->getComposer()->getRepositoryManager()->getLocalRepository();
        foreach ($localRepo->getCanonicalPackages() as $package) {
            $this->getHandler($event->getComposer(), $event->getIO())->handle($package);
        }
    }

    public function handlePackageEvent(PackageEvent $event): void
    {
        $package = match (\get_class($event->getOperation())) {
            InstallOperation::class => $event->getOperation()->getPackage(),
            UpdateOperation::class => $event->getOperation()->getTargetPackage(),
            default => throw new OutOfRangeException(sprintf('Operation %s not supported', $event->getOperation()->getOperationType()))
        };
        $this->getHandler($event->getComposer(), $event->getIO())->handle($package);
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->addInstallers($composer, $io);
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    private function addInstallers(Composer $composer, IOInterface $io): void
    {
        $installers = [
            new ArchiveInstaller($io, $composer),
            new FileInstaller($io, $composer),
        ];
        $installationManager = $composer->getInstallationManager();
        foreach ($installers as $installer) {
            $installationManager->addInstaller($installer);
        }
    }

    private function getHandler(Composer $composer, IOInterface $io): PackageHandlerInterface
    {
        $this->handler ??= new PackageHandler($composer, $io);

        return $this->handler;
    }
}
