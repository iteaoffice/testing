<?php

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
