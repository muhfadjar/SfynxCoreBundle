<?php
namespace Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Response;

use Exception;
use Symfony\Component\Form\FormInterface as FormViewInterface;
use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Response\AbstractCreateFormView;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;

/**
 * Class OBCreateFormView
 *
 * @category Sfynx\CoreBundle\Layers
 * @package Domain
 * @subpackage Workflow\Observer\Response
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBCreateFormView extends AbstractCreateFormView
{
    /**
     * The process function create form view
     *
     * @return bool False to notify that postprocessing could not be executed.
     * @throws WorkflowException
     */
    protected function process(): bool
    {
        try {
            $this->wfLastData->form = $this->createForm();
        } catch (Exception $e) {
            throw WorkflowException::noCreatedForm();
        }
        return true;
    }

    protected function createForm(): FormViewInterface
    {
        $this->formType->setData($this->wfLastData->formViewData);
        return $this->formFactory->create($this->formType);
    }
}
