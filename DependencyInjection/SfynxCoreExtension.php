<?php
/**
 * This file is part of the <Core> project.
 *
 * @category   Core
 * @package    DependencyInjection
 * @subpackage Extension
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2015-02-16
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sfynx\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader,
    Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @category   Core
 * @package    DependencyInjection
 * @subpackage Extension
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2015-02-16
 */
class SfynxCoreExtension extends Extension
{
    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {
        // we load all services
        $loaderYaml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loaderYaml->load('service/services_cmd.yml');
        $loaderYaml->load('service/services_request.yml');
        $loaderYaml->load('service/services_util.yml');
        $loaderYaml->load('service/services_cmfconfig.yml');

        // we load all controller parameters
        $loaderYaml->load('controller/crud_command.yml');
        $loaderYaml->load('controller/crud_query.yml');

        // we load config
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);
        
        /**
         * Cookies config parameter
         */
        if (isset($config['cookies'])) {
        	if (isset($config['cookies']['date_expire'])) {
        		$container->setParameter('sfynx.core.cookies.date_expire', $config['cookies']['date_expire']);
        	}
        	if (isset($config['cookies']['date_interval'])) {
        		$container->setParameter('sfynx.core.cookies.date_interval',$config['cookies']['date_interval']);
        	}
        	if (isset($config['cookies']['application_id'])) {
        		$container->setParameter('sfynx.core.cookies.application_id',$config['cookies']['application_id']);
        	}
        }        

        /**
         * Translation config parameter
         */
        if (isset($config['translation'])){
        	if (isset($config['translation']['defaultlocale_setting'])) {
        		$container->setParameter('sfynx.core.translation.defaultlocale', $config['translation']['defaultlocale_setting']);
        	} else {
        		$container->setParameter('sfynx.core.translation.defaultlocale', true);
        	}
        }        
        
        /**
         * Permission config parameter
         */
        if (isset($config['permission'])) {
        	if (isset($config['permission']['restriction_by_roles'])) {
        		$container->setParameter('sfynx.core.permission.restriction_by_roles', $config['permission']['restriction_by_roles']);
        	}
        	if (isset($config['permission']['authorization']) && isset($config['permission']['authorization']['prepersist'])) {
        		$container->setParameter('sfynx.core.permission.authorization.prepersist', $config['permission']['authorization']['prepersist']);
        	}
        	if (isset($config['permission']['authorization']) && isset($config['permission']['authorization']['preupdate'])) {
        		$container->setParameter('sfynx.core.permission.authorization.preupdate', $config['permission']['authorization']['preupdate']);
        	}
        	if (isset($config['permission']['authorization']) && isset($config['permission']['authorization']['preremove'])) {
        		$container->setParameter('sfynx.core.permission.authorization.preremove', $config['permission']['authorization']['preremove']);
        	}
        	if (isset($config['permission']['prohibition']) && isset($config['permission']['prohibition']['preupdate'])) {
        		$container->setParameter('sfynx.core.permission.prohibition.preupdate', $config['permission']['prohibition']['preupdate']);
        	}
        	if (isset($config['permission']['prohibition']) && isset($config['permission']['prohibition']['preremove'])) {
        		$container->setParameter('sfynx.core.permission.prohibition.preremove', $config['permission']['prohibition']['preremove']);
        	}
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
    	return 'sfynx_core';
    }
}
