<?php
namespace PoP\ComponentModel\ModuleProcessors;

use PoP\Application\QueryInputOutputHandlers\ActionExecutionQueryInputOutputHandler;

trait QueryDataModuleProcessorTrait
{
    use PoP\ComponentModel\ModuleProcessors\QueryDataModuleProcessorTrait;

    public function getQueryInputOutputHandlerClass(array $module): ?string
    {
        return ActionExecutionQueryInputOutputHandler::class;
    }
}
