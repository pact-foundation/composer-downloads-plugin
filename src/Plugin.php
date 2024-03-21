<?php

/*
 * This file is part of Composer Extra Files Plugin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\DownloadsPlugin;

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
use LastCall\DownloadsPlugin\Exception\OperationNotSupportedException;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private const EVENT_PRIORITY = 10;
    private PackageInstaller $installer;

    public function __construct(?PackageInstaller $installer = null)
    {
        $this->installer = $installer ?? new PackageInstaller();
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
        $this->installer->install($rootPackage, $event->getComposer(), $event->getIO());

        // Ensure that any other packages are properly reconciled.
        $localRepo = $event->getComposer()->getRepositoryManager()->getLocalRepository();
        foreach ($localRepo->getCanonicalPackages() as $package) {
            $this->installer->install($package, $event->getComposer(), $event->getIO());
        }
    }

    public function handlePackageEvent(PackageEvent $event): void
    {
        $package = match (\get_class($event->getOperation())) {
            InstallOperation::class => $event->getOperation()->getPackage(),
            UpdateOperation::class => $event->getOperation()->getTargetPackage(),
            default => throw new OperationNotSupportedException(sprintf('Operation %s not supported', $event->getOperation()->getOperationType()))
        };
        $this->installer->install($package, $event->getComposer(), $event->getIO());
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
