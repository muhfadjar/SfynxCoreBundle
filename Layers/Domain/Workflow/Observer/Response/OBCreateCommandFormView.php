<?php
namespace Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Response;

use Exception;
use Symfony\Component\Form\FormInterface as FormViewInterface;
use Symfony\Component\HttpKernel\Kernel;
use Sfynx\CoreBundle\Layers\Application\Validation\Type\Generalisation\Interfaces\FormTypeInterface;
use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Response\AbstractCreateFormView;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;

/**
 * Class OBCreateCommandFormView
 *
 * @category Sfynx\CoreBundle\Layers
 * @package Domain
 * @subpackage Workflow\Observer\Response
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright 2016 PI-GROUPE
 */
class OBCreateCommandFormView extends AbstractCreateFormView
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

    /**
     * @return FormViewInterface
     */
    protected function createForm(): FormViewInterface
    {
        $this->formType->setData($this->wfLastData->formViewData);

        if ((Kernel::MAJOR_VERSION >= 3)
            && (Kernel::MINOR_VERSION >= 3)
            && $this->formType instanceof FormTypeInterface
        ) {
            return $this->formFactory->create(\get_class($this->formType), $this->wfHandler->command);
        }
        return $this->formFactory->create($this->formType, $this->wfHandler->command);
    }
}
