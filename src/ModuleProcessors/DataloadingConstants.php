<?php
namespace PoP\Application\ModuleProcessors;

class DataloadingConstants extends \PoP\ComponentModel\ModuleProcessors\DataloadingConstants {
    const LAZYLOAD = 'lazy-load';
    const EXTERNALLOAD = 'external-load';
    const USEMOCKDBOBJECTDATA = 'use-mock-dbobject-data';
}
