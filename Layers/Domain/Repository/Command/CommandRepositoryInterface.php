<?php
namespace Sfynx\CoreBundle\Layers\Domain\Repository\Command;

use Doctrine\ORM\EntityManagerInterface;

use Sfynx\CoreBundle\Layers\Domain\Repository\ResultFunctionRepositoryInterface;
use Sfynx\CoreBundle\Layers\Domain\Repository\ProviderRepositoryInterface;
use Sfynx\CoreBundle\Layers\Domain\Repository\Command\SaveRepositoryInterface;
use Sfynx\CoreBundle\Layers\Domain\Repository\Query\GeneralRepositoryInterface;
use Sfynx\CoreBundle\Layers\Infrastructure\Cache\CacheQuery;

/**
 * Command Repository Interface
 *
 * @category   Sfynx\CoreBundle\Layers
 * @package    Domain
 * @subpackage Repository\Command
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2015-02-16
 */
interface CommandRepositoryInterface extends ResultFunctionRepositoryInterface, ProviderRepositoryInterface, SaveRepositoryInterface, GeneralRepositoryInterface
{
    /**
     * @return CacheQuery
     */
    public function getCacheFactory(): CacheQuery;

    /**
     * @return EntityManagerInterface
     */
    public function getEm(): EntityManagerInterface;

    /**
     * @return string
     */
    public function getEntityName(): string;

    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * @return CommandRepositoryInterface
     */
    public function setIdGenerator(): CommandRepositoryInterface;
}
