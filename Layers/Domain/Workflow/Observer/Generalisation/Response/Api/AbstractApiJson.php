<?php
namespace Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Response\Api;

use Exception;
use stdClass;
use Symfony\Component\Translation\TranslatorInterface;
use Sfynx\ToolBundle\Twig\Extension\PiToolExtension;
use Sfynx\ToolBundle\Builder\RouteTranslatorFactoryInterface;
use Sfynx\AuthBundle\Domain\Service\Role\Generalisation\RoleFactoryInterface;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;
use Sfynx\CoreBundle\Layers\Domain\Specification\SpecIsObjectCreatedWithHandlerInterface;
use Sfynx\CoreBundle\Layers\Domain\Specification\SpecIsValidRequest;
use Sfynx\CoreBundle\Layers\Domain\Specification\SpecIsHandlerCreatedWithNoRedirection;
use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Response\AbstractObserver;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\WorkflowException;

/**
 * Abstract Class AbstractCreateIndexBodyJson
 *
 * @category Sfynx\CoreBundle\Layers
 * @package Domain
 * @subpackage Workflow\Observer\Generalisation\Response
 * @abstract
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
abstract class AbstractApiJson extends AbstractObserver
{
    /** @var RequestInterface */
    protected $request;
    /** @var RoleFactoryInterface*/
    protected $roleFactory;
    /** @var PiToolExtension*/
    protected $toolExtension;
    /** @var RouteTranslatorFactoryInterface */
    protected $routeFactory;
    /** @var TranslatorInterface */
    protected $translator;
    /** @var stdClass */
    protected $object;
    /** @var stdclass */
    protected $param;

    /**
     * AbstractCreateIndexBodyJson constructor.
     *
     * @param RequestInterface $request
     * @param null|RouteTranslatorFactoryInterface $routeFactory
     * @param null|array $param
     * @param null|RoleFactoryInterface $roleFactory
     * @param null|TranslatorInterface $translator
     * @param null|PiToolExtension $toolExtension
     */
    public function __construct(
        RequestInterface $request,
        RouteTranslatorFactoryInterface $routeFactory = null,
        stdclass $param = null,
        RoleFactoryInterface $roleFactory = null,
        TranslatorInterface $translator = null,
        PiToolExtension $toolExtension = null
    ) {
        $this->request = $request;
        $this->roleFactory = $roleFactory;
        $this->toolExtension = $toolExtension;
        $this->routeFactory = $routeFactory;
        $this->translator = $translator;

        $this->object = new stdClass();
        $this->param = $param;
    }

    /**
     * @return array List of accepted request methods ['POST', 'GET', ...]
     */
    protected function getValidMethods(): array
    {
        return ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    }

    /**
     * Prepare object attributs values used by class specifications
     */
    protected function prepareObject()
    {
        $this->object->requestMethod = $this->request->getMethod();
        $this->object->validMethod = $this->getValidMethods();
        $this->object->handler = $this->wfHandler;
        $this->object->param = $this->param;
    }

    /**
     * Sends a persists action create to specific create manager.
     * @return AbstractObserver
     * @throws WorkflowException
     */
    protected function execute(): AbstractObserver
    {
        // preapre object attribut used by specifications
        $this->prepareObject();
        // we abort if we are not in the create form process
        $specs = (new SpecIsValidRequest())
            ->AndSpec(new SpecIsObjectCreatedWithHandlerInterface())
            ->AndSpec(new SpecIsHandlerCreatedWithNoRedirection())
        ;
        if (!$specs->isSatisfiedBy($this->object)) {
            return $this;
        }
        // we run edit form process
        $this->process();

        return $this;
    }

    /**
     * The process function ...
     *
     * @return bool False to notify that postprocessing could not be executed.
     * @throws WorkflowException
     */
    abstract protected function process(): bool;

    /**
     * Return the url of a route, with or without a locale value
     *
     * @param string $routeName
     * @param string $params
     * @return string
     */
    protected function generateUrl($routeName, $params = null)
    {
        return $this->routeFactory->generate($routeName, $params);
    }
}
