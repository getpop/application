<?php

declare(strict_types=1);

namespace PoP\Application\LooseContracts;

use PoP\LooseContracts\AbstractLooseContractSet;

class LooseContractSet extends AbstractLooseContractSet
{
    public function getRequiredNames()
    {
        return [
            // Options
            'popcms:option:limit',
        ];
    }
}
