<?php

/*
 * This file is part of the distributed-configuration-bundle package
 *
 * Copyright (c) 2016 Guillaume Cavana
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Guillaume Cavana <guillaume.cavana@gmail.com>
 */

namespace Maikuro\DistributedConfigurationBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MaikuroDistributedConfigurationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
    }
}
