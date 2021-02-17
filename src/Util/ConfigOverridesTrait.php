<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

namespace Testing\Util;

/**
 * Class ConfigOverridesTrait
 *
 * @package Testing\Util
 */
trait ConfigOverridesTrait
{
    private $configOverrides = [];

    protected function getConfigOverrides(): array
    {
        return $this->configOverrides;
    }

    protected function setConfigOverrides(array $configOverrides)
    {
        $this->configOverrides = $configOverrides;
    }
}
