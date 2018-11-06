<?php
namespace Sfynx\CoreBundle\Layers\Domain\Specification;

use stdClass;
use Sfynx\SpecificationBundle\Specification\AbstractSpecification;


/**
 * Class SpecIsHandlerCreatedWithEntityInterface
 *
 * @category Sfynx\CoreBundle\Layers
 * @package Domain
 * @subpackage Specification
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class SpecIsHandlerCreatedWithEntityInterface extends AbstractSpecification
{
    /**
     * return true if we are in the editing process
     *
     * @param stdClass $object
     * @return bool
     */
    public function isSatisfiedBy(stdClass $object): bool
    {
        return \property_exists($object->handler, 'entity')
            && \is_object($object->handler->entity);
    }
}
