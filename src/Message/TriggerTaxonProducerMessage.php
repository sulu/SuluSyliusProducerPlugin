<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\Message;

class TriggerTaxonProducerMessage
{
    /**
     * @param int[] $taxonIds
     */
    public function __construct(
        public array $taxonIds,
    ) {
    }
}
