<?php
namespace PoP\Application\LooseContracts;

use PoP\LooseContracts\Contracts\AbstractLooseContractSet;

class LooseContractSet extends AbstractLooseContractSet
{
    public function getRequiredNames() {
		return [
            // Options
			'popcms:option:limit',
        ];
	}
}
