<?php
namespace Sfynx\CoreBundle\Layers\Infrastructure\Exception;

use Exception;

/**
 * Exception Class WorkflowException
 *
 * @category Sfynx\CoreBundle\Layers
 * @package Infrastructure
 * @subpackage Exception
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class WorkflowException extends Exception
{
    /**
     * Returns the <No Created Entity> Exception.
     *
     * @return WorkflowException
     */
    public static function noCreatedEntity(): WorkflowException
    {
        return new static('Entity has not been created');
    }

    /**
     * Returns the <No Created Form> Exception.
     *
     * @return WorkflowException
     */
    public static function noCreatedForm(): WorkflowException
    {
        return new static('Form has not been created');
    }

    /**
     * Returns the <No Created Form Data> Exception.
     *
     * @return WorkflowException
     */
    public static function noCreatedFormData(): WorkflowException
    {
        return new static('Form data has not been created');
    }

    /**
     * Returns the <No Created View Form> Exception.
     *
     * @return WorkflowException
     */
    public static function noCreatedViewForm(): WorkflowException
    {
        return new static('View form has not been created');
    }

    /**
     * Returns the <No Entity Instances> Exception.
     *
     * @return WorkflowException
     */
    public static function noEntityInstances(): WorkflowException
    {
        return new static('Instances of Entity have not been listed');
    }

    /**
     * Returns the <No Created View Form> Exception.
     *
     * @return WorkflowException
     */
    public static function noBodyJsonImplementCorrectly(): WorkflowException
    {
        return new static('Json body has not been implement correctly');
    }
}
