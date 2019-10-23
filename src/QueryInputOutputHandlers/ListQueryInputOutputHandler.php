<?php
namespace PoP\Application\QueryInputOutputHandlers;
use PoP\ComponentModel\QueryInputOutputHandlers\AbstractQueryInputOutputHandler;
use PoP\Application\ModuleProcessors\DataloadingConstants;
use PoP\LooseContracts\Facades\Contracts\NameResolverFacade;

class ListQueryInputOutputHandler extends AbstractQueryInputOutputHandler
{
    public function prepareQueryArgs(&$query_args)
    {
        parent::prepareQueryArgs($query_args);

        // Handle edge cases for the limit (for security measures)
        $cmsengineapi = \PoP\Engine\FunctionAPIFactory::getInstance();
        $configuredLimit = $cmsengineapi->getOption(NameResolverFacade::getInstance()->getName('popcms:option:limit'));
        if (isset($query_args[GD_URLPARAM_LIMIT])) {
            $limit = $query_args[GD_URLPARAM_LIMIT];
            if ($limit === -1 || $limit === 0) {
                // Avoid users querying all results (by passing limit=-1 or limit=0)
                $limit = $configuredLimit;
            } elseif ($limit > $configuredLimit * 10) {
                // Do not allow more than 10 times the set amount
                $limit = $configuredLimit * 10;
            }
        } else {
            $limit = $configuredLimit;
        }
        $query_args[GD_URLPARAM_LIMIT] = intval($limit);
        $query_args[GD_URLPARAM_PAGENUMBER] = $query_args[GD_URLPARAM_PAGENUMBER] ? intval($query_args[GD_URLPARAM_PAGENUMBER]) : 1;
    }

    public function getQueryState($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbObjectIDOrIDs): array
    {
        $ret = parent::getQueryState($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbObjectIDOrIDs);
        $vars = \PoP\ComponentModel\Engine_Vars::getVars();

        // Needed to loadLatest, to know from what time to get results
        if ($data_properties[DataloadingConstants::DATASOURCE] == POP_DATALOAD_DATASOURCE_MUTABLEONREQUEST) {
            $ret[GD_URLPARAM_TIMESTAMP] = POP_CONSTANT_CURRENTTIMESTAMP;
        }

        // If it is lazy load, no need to calculate pagenumber / stop-fetching / etc
        if ($data_properties[DataloadingConstants::LAZYLOAD] || $data_properties[DataloadingConstants::EXTERNALLOAD] || $data_properties[DataloadingConstants::DATASOURCE] != POP_DATALOAD_DATASOURCE_MUTABLEONREQUEST || $vars['loading-latest']) {
            return $ret;
        }

        // If data is not to be loaded, then "stop-fetching" as to not show the Load More button
        if ($data_properties[DataloadingConstants::SKIPDATALOAD]) {
            $ret[GD_URLPARAM_STOPFETCHING] = true;
            return $ret;
        }

        $ret[GD_URLPARAM_STOPFETCHING] = Utils::stopFetching($dbObjectIDOrIDs, $data_properties);

        return $ret;
    }

    public function getQueryParams($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbObjectIDOrIDs): array
    {
        $ret = parent::getQueryParams($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbObjectIDOrIDs);
        $vars = \PoP\ComponentModel\Engine_Vars::getVars();

        // If data is not to be loaded, then "stop-fetching" as to not show the Load More button
        if ($data_properties[DataloadingConstants::SKIPDATALOAD] || $data_properties[DataloadingConstants::DATASOURCE] != POP_DATALOAD_DATASOURCE_MUTABLEONREQUEST) {
            return $ret;
        }

        $query_args = $data_properties[DataloadingConstants::QUERYARGS];

        if ($limit = $query_args[GD_URLPARAM_LIMIT]) {
            $ret[GD_URLPARAM_LIMIT] = $limit;
        }

        $pagenumber = $query_args[GD_URLPARAM_PAGENUMBER];
        if (!Utils::stopFetching($dbObjectIDOrIDs, $data_properties)) {
            // When loading latest, we need to return the same $pagenumber as we got, because it must not alter the params
            $nextpagenumber = ($vars['loading-latest']) ? $pagenumber : $pagenumber + 1;
        }
        $ret[GD_URLPARAM_PAGENUMBER] = $nextpagenumber;

        return $ret;
    }

    // function getUniquetodomainQuerystate($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbobjectids) {

    //     $ret = parent::getUniquetodomainQuerystate($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbobjectids);

    //     // Needed to loadLatest, to know from what time to get results
    //     $ret[GD_URLPARAM_TIMESTAMP] = POP_CONSTANT_CURRENTTIMESTAMP;

    //     // If data is not to be loaded, then "stop-fetching" as to not show the Load More button
    //     if ($data_properties[DataloadingConstants::SKIPDATALOAD]) {

    //         $ret[GD_URLPARAM_STOPFETCHING] = true;
    //         return $ret;
    //     }

    //     // If it is lazy load, no need to calculate pagenumber / stop-fetching / etc
    //     if ($data_properties[DataloadingConstants::LAZYLOAD]) {

    //         return $ret;
    //     }

    //     // If loading static data, then that's it
    //     if ($data_properties[DataloadingConstants::DATASOURCE] != POP_DATALOAD_DATASOURCE_MUTABLEONREQUEST) {

    //         return $ret;
    //     }

    //     $query_args = $data_properties[DataloadingConstants::QUERYARGS];
    //     $pagenumber = $query_args[GD_URLPARAM_PAGENUMBER];
    //     $stop_loading = Utils::stopFetching($dbobjectids, $data_properties);

    //     $ret[GD_URLPARAM_STOPFETCHING] = $stop_loading;

    //     // When loading latest, we need to return the same $pagenumber as we got, because it must not alter the params
    //     $nextpaged = $vars['loading-latest'] ? $pagenumber : $pagenumber + 1;
    //     $ret[ParamConstants::PARAMS][GD_URLPARAM_PAGENUMBER] = $stop_loading ? '' : $nextpaged;

    //     // Do not send this value back when doing loadLatest, or it will mess up the original structure loading
    //     // Doing 'unset' as to also take it out if an ancestor class (eg: GD_DataLoad_BlockQueryInputOutputHandler) has set it
    //     if ($vars['loading-latest']) {

    //         unset($ret[GD_URLPARAM_STOPFETCHING]);
    //     }

    //     return $ret;
    // }

    // function getSharedbydomainsQuerystate($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbobjectids) {

    //     $ret = parent::getSharedbydomainsQuerystate($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbobjectids);

    //     $query_args = $data_properties[DataloadingConstants::QUERYARGS];

    //     $limit = $query_args[GD_URLPARAM_LIMIT];
    //     $ret[ParamConstants::PARAMS][GD_URLPARAM_LIMIT] = $limit;

    //     return $ret;
    // }

    // function getDatafeedback($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbobjectids) {

    //     $ret = parent::getDatafeedback($data_properties, $dataaccess_checkpoint_validation, $actionexecution_checkpoint_validation, $executed, $dbobjectids);

    //     $query_args = $data_properties[DataloadingConstants::QUERYARGS];

    //     $limit = $query_args[GD_URLPARAM_LIMIT];
    //     $ret[ParamConstants::PARAMS][GD_URLPARAM_LIMIT] = $limit;

    //     // If it is lazy load, no need to calculate show-msg / pagenumber / stop-fetching / etc
    //     if ($data_properties[DataloadingConstants::LAZYLOAD]) {

    //         return $ret;
    //     }

    //     $pagenumber = $query_args[GD_URLPARAM_PAGENUMBER];

    //     // Print feedback messages always, if none then an empty array
    //     $msgs = array();

    //     // Show error message if no items, but only if the checkpoint did not fail
    //     $checkpoint_failed = \PoP\ComponentModel\GeneralUtils::isError($dataaccess_checkpoint_validation);
    //     if (!$checkpoint_failed) {
    //         if (empty($dbobjectids)) {

    //             // Do not show the message when doing loadLatest
    //             if (!$vars['loading-latest']) {

    //                 // If pagenumber < 2 => There are no results at all
    //                 $msgs[] = array(
    //                     'codes' => array(
    //                         ($pagenumber < 2) ? 'noresults' : 'nomore',
    //                     ),
    //                     GD_JS_CLASS => 'alert-warning',
    //                 );
    //             }
    //         }
    //     }
    //     $ret['msgs'] = $msgs;

    //     // stop-fetching is loaded twice: in the params and in the feedback. This is because we can't access the params from the .tmpl files
    //     // (the params object is created only when initializing JS => after rendering the html with Handlebars so it's not available by then)
    //     // and this value is needed in fetchmore.tmpl
    //     $stop_loading = Utils::stopFetching($dbobjectids, $data_properties);

    //     $ret[GD_URLPARAM_STOPFETCHING] = $stop_loading;

    //     // Add the Fetch more link for the Search Engine
    //     if (!$stop_loading && $data_properties[DataloadingConstants::SOURCE]) {

    //         $ret[POP_IOCONSTANT_QUERYNEXTURL] = add_query_arg(GD_URLPARAM_PAGENUMBER, $pagenumber+1, $data_properties[DataloadingConstants::SOURCE]);
    //     }

    //     // Do not send this value back when doing loadLatest, or it will mess up the original structure loading
    //     // Doing 'unset' as to also take it out if an ancestor class (eg: GD_DataLoad_BlockQueryInputOutputHandler) has set it
    //     if ($vars['loading-latest']) {

    //         unset($ret[GD_URLPARAM_STOPFETCHING]);
    //     }

    //     return $ret;
    // }
}