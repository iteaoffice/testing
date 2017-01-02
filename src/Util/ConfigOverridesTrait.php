<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Admin
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Testing\Util;

/**
 * Class ConfigOverridesTrait
 *
 * @package Testing\Util
 */
trait ConfigOverridesTrait
{

    /**
     * @var array
     */
    private $configOverrides = [];

    /**
     * @return array
     */
    protected function getConfigOverrides(): array
    {
        return $this->configOverrides;
    }

    /**
     * @param array $configOverrides
     */
    protected function setConfigOverrides(array $configOverrides)
    {
        $this->configOverrides = $configOverrides;
    }
}