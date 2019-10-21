<?php
namespace PoP\Application\QueryInputOutputHandlers;
use PoP\ComponentModel\QueryInputOutputHandlers\AbstractQueryInputOutputHandler;

class ActionExecutionQueryInputOutputHandler extends AbstractQueryInputOutputHandler
{
    public function getQueryResult($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbObjectIDOrIDs): array
    {
        $ret = parent::getQueryResult($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbObjectIDOrIDs);

        if ($executed) {
            // $executed may contain strings "success", "successstrings", "softredirect", etc
            $ret = array_merge(
                $ret,
                $executed
            );
        }

        return $ret;
    }
}
