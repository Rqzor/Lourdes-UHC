<?php

declare(strict_types=1);

namespace uhc\scenarios\modules\backpacks;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\metadata\SingleBlockMenuMetadata;

/**
 * Class BackpackMenuMetadata
 * @package uhc\scenarios\modules\backpacks
 */
class BackpackMenuMetadata extends SingleBlockMenuMetadata
{

    /**
     * @return InvMenuInventory
     */
    public function createInventory(): InvMenuInventory
    {
        return new BackpackInventory($this);
    }
}