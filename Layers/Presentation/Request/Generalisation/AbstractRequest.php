<?php
namespace Sfynx\CoreBundle\Layers\Presentation\Request\Generalisation;

use stdClass;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sfynx\CoreBundle\Layers\Presentation\Request\Generalisation\Interfaces\RequestInterface as PresRequestInterface;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;
use Sfynx\CoreBundle\Layers\Domain\Specification\SpecIsValidRequest;

/**
 * Class AbstractRequest
 *
 * @category   Sfynx\CoreBundle\Layers
 * @package    Presentation
 * @subpackage Request\Generalisation
 * @abstract
 */
abstract class AbstractRequest implements PresRequestInterface
{
    /** @var array */
    protected $defaults = [];
    /** @var array */
    protected $required = [];
    /** @var array  */
    protected $allowedTypes = [];
    /** @var array */
    protected $allowedValues = [];
    /** @var array */
    protected $requestParameters = [];
    /** @var array */
    protected $options;
    /** @var array */
    protected $parameters;
    /** @var RequestInterface */
    protected $request;
    /** @var stdClass */
    protected $object;

    /**
     * AbstractFormRequest constructor.
     * @param RequestInterface $request
     * @param array $parameters
     */
    public function __construct(RequestInterface $request, array $parameters = [])
    {
        $this->request  = $request;
        $this->parameters  = $parameters;
        $this->object = new stdClass();

        $this->execute();
    }

    /**
     * @return array
     */
    protected function getNormalizers(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * Prepare object attributs values used by class specifications
     */
    protected function prepareObject()
    {
        $this->object->requestMethod = $this->request->getMethod();
        $this->object->validMethod = $this->getValidMethods();
    }

    /**
     *
     * @return AbstractFormRequest
     */
    protected function execute(): AbstractRequest
    {
        // preapre object attribut used by specifications
        $this->prepareObject();
        // we abort if we are not in the edit form process
        $specs = new SpecIsValidRequest();
        if (!$specs->isSatisfiedBy($this->object)) {
            return $this;
        }
        // we run edit form process
        $this->process();

        return $this;
    }

    /**
     * @return void
     */
    protected function process(): void
    {
        $this->setOptions();

        $mt = $this->object->requestMethod;
        foreach (['defaults', 'required', 'allowedTypes', 'allowedValues'] as $attribut) {
            if (isset($this->$attribut[$mt])
                && \is_string($this->$attribut[$mt])
                && isset($this->$attribut[$this->$attribut[$mt]])
            ) {
                $this->$attribut[$mt] = $this->$attribut[$this->$attribut[$mt]];
            }
            $this->$attribut = isset($this->$attribut[$mt]) ? $this->$attribut[$mt] : $this->$attribut;
        }

        /* multidimensional resolver */
        $this->multidimensionalOtionResolver($this->options, $this->defaults, $this->allowedTypes, $this->allowedValues);

        /* main resolver */
        $this->mainOptionResolver();
    }

    /**
     * @return array List of accepted request methods ['POST', 'GET', ...]
     */
    protected function getValidMethods(): array
    {
        return ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
    }

    /**
     * @return void
     */
    protected function setOptions()
    {
        $this->options = \json_decode($this->request->getContent(), true);
        $this->options = (null !== $this->options) ? $this->options : [];
        $this->options = \array_merge($this->options, $this->parameters);
    }

    /**
     * @param array $options
     * @param array $defaults
     * @param array $allowedTypes
     * @param array $allowedValues
     */
    protected function multidimensionalOtionResolver(
        array &$options,
        array &$defaults = [],
        array &$allowedTypes = [],
        array &$allowedValues = []
    ): void {
        $multidimensionalDefaults = [];
        $multidimensionalAllowedType = [];
        $multidimensionalAllowedValues = [];
        foreach ($defaults as $field => $optionDefault) {
            if (\is_array($optionDefault)) {
                $this->requestParameters[$field] = $this->requestParameters[$field] ?? [];
                $options[$field] = $options[$field] ?? [];
                $defaults[$field] = $defaults[$field] ?? [];
                $allowedTypes[$field] = $allowedTypes[$field] ?? [];
                $allowedValues[$field] = $allowedValues[$field] ?? [];

                $this->multidimensionalOtionResolver(
                    $options[$field],
                    $defaults[$field],
                    $allowedTypes[$field] ,
                    $allowedValues[$field]
                );

                $multidimensionalDefaults[$field] = $optionDefault;
                $defaults[$field] = null;

                if (isset($allowedTypes[$field])) {
                    $multidimensionalAllowedType[$field] = $allowedTypes[$field];
                    $allowedTypes[$field] = ['array', 'null'];
                }
                if (isset($allowedValues[$field])) {
                    $multidimensionalAllowedValues[$field] = $allowedValues[$field];
                    unset($allowedValues[$field]);
                }
            }
        }
        foreach ($multidimensionalDefaults as $field => $optionDefault) {
            $fieldResolver = new OptionsResolver();
            $fieldResolver->setDefaults($optionDefault);

            if (!empty($multidimensionalAllowedType[$field])) {
                foreach ($multidimensionalAllowedType[$field] as $optionName => $optionTypes) {
                    $fieldResolver->setAllowedTypes($optionName, $optionTypes);
                }
            }
            if (!empty($multidimensionalAllowedValues[$field])) {
                foreach ($multidimensionalAllowedValues[$field] as $optionName => $optionTypes) {
                    $fieldResolver->setAllowedValues($optionName, $optionTypes);
                }
            }

            $options[$field] = $options[$field] ?? [];
            $this->requestParameters[$field] = $fieldResolver->resolve($options[$field]);
        }
    }

    /**
     * @return voids
     */
    protected function mainOptionResolver(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults($this->defaults);
        $resolver->setRequired($this->required);

        foreach ($this->allowedTypes as $optionName => $optionTypes) {
            $resolver->setAllowedTypes($optionName, $optionTypes);
        }
        foreach ($this->allowedValues as $optionName => $optionValues) {
            $resolver->setAllowedValues($optionName, $optionValues);
        }
        foreach ($this->getNormalizers() as $optionName => $optionValues) {
            $resolver->setNormalizer($optionName, $optionValues);
        }
        $this->requestParameters = \array_merge($resolver->resolve($this->options), $this->requestParameters);
    }
}
