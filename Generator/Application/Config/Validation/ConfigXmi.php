<?php

/*
 * (c) Etienne de Longeaux <etienne.delongeaux@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sfynx\CoreBundle\Generator\Application\Config\Validation;

use Sfynx\CoreBundle\Generator\Application\Config\Config;
use Sfynx\CoreBundle\Generator\Application\Config\Generalisation\ValidationInterface;

/**
 * Config XMI
 * @package Sfynx\CoreBundle\Generator\Application\Config\Validation
 *
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class ConfigXmi implements ValidationInterface
{
    /**
     * @inheritdoc
     */
    public function validate(Config $config)
    {
        if ($config->has('report-xmi')) {
            $argv = \explode('|', $config->get('report-xmi'));
            $config->set('report-xmi', $argv);
        }
    }
}