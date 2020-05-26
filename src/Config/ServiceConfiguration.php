<?php

declare(strict_types=1);

namespace PoP\Application\Config;

use PoP\ComponentModel\Modules\DefinitionGroups;
use PoP\Root\Component\PHPServiceConfigurationTrait;
use PoP\ComponentModel\Container\ContainerBuilderUtils;

class ServiceConfiguration
{
    use PHPServiceConfigurationTrait;

    protected static function configure(): void
    {
        // Add ModuleFilter to the ModuleFilterManager
        ContainerBuilderUtils::injectServicesIntoService(
            'module_filter_manager',
            'PoP\\Application\\ModuleFilters',
            'add'
        );

        // Set the definition resolver
        ContainerBuilderUtils::injectValuesIntoService(
            'definition_manager',
            'setDefinitionResolver',
            '@emoji_definition_resolver',
            DefinitionGroups::MODULES
        );
    }
}
