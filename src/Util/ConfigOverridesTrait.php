<?php

namespace Testing\Util;

/**
 * Class ConfigOverridesTrait
 *
 * @package Testing\Util
 */
trait ConfigOverridesTrait
{
    private array $configOverrides = [];

    protected function getConfigOverrides(): array
    {
        return $this->configOverrides;
    }

    protected function setConfigOverrides(array $configOverrides): void
    {
        $this->configOverrides = $configOverrides;
    }
}
