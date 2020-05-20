<?php

declare(strict_types=1);

namespace PoP\Application;

use PoP\Root\Component\AbstractComponent;
use PoP\Root\Component\YAMLServicesTrait;
use PoP\Application\Config\ServiceConfiguration;
use PoP\ComponentModel\Container\ContainerBuilderUtils;

/**
 * Initialize component
 */
class Component extends AbstractComponent
{
    use YAMLServicesTrait;
    // const VERSION = '0.1.0';

    public static function getDependedComponentClasses(): array
    {
        return [
            // \PoP\ComponentModelConfiguration\Component::class,
            \PoP\API\Component::class,
            \PoP\EmojiDefinitions\Component::class,
            \PoP\DefinitionPersistence\Component::class,
        ];
    }

    public static function getDependedMigrationPlugins(): array
    {
        return [
            'migrate-component-model-configuration',
        ];
    }

    /**
     * Initialize services
     */
    protected static function doInitialize()
    {
        parent::doInitialize();
        self::initYAMLServices(dirname(__DIR__));
        ServiceConfiguration::initialize();
    }

    /**
     * Boot component
     *
     * @return void
     */
    public static function beforeBoot()
    {
        parent::beforeBoot();

        // Initialize classes
        ContainerBuilderUtils::instantiateNamespaceServices(__NAMESPACE__ . '\\Hooks');
        ContainerBuilderUtils::instantiateNamespaceServices(__NAMESPACE__ . '\\LooseContracts');
    }
}
