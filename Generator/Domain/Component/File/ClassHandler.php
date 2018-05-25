<?php
namespace Sfynx\CoreBundle\Generator\Domain\Component\File;

use stdClass;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Sfynx\CoreBundle\Generator\Domain\Templater\Generalisation\Interfaces\TemplaterInterface;

/**
 * File finder
 * @category   Sfynx\CoreBundle\Generator
 * @package    Domain
 * @subpackage Component\File
 *
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class ClassHandler
{
    /** @var array */
    public static $constructorArguments = [];

    /**
     * @inheritdoc
     */
    public static function getDirenameFromNamespace(string $namespace): string
    {
        if (strrpos($namespace, '\\')) {
            return substr($namespace, 0, -strlen($namespace) + strrpos($namespace, '\\'));
        }

        return $namespace;
    }

    /**
     * @inheritdoc
     */
    public static function getClassNameFromNamespace(string $namespace): string
    {
        if (strrpos($namespace, '\\')) {
            return substr($namespace, strrpos($namespace, '\\') + 1);
        }

        return $namespace;
    }

    /**
     * @param string $namespace
     * @return PhpNamespace
     * @static
     */
    public static function getNamespace(string $namespace): PhpNamespace
    {
        return new PhpNamespace($namespace);
    }

    /**
     * @param PhpNamespace $namespace
     * @param stdClass $data
     * @param array|null $index
     * @param string $context
     * @return void
     * @static
     */
    public static function addUses(PhpNamespace $namespace, stdClass $data, ?array $index = [], string $context = ''): void
    {
        if (property_exists($data, 'options')
            && property_exists($data->options, 'uses')
            && $data->options->uses
        ) {
            foreach ($data->options->uses as $use) {
                self::addUse($namespace, $use, $index, $context);
            }
        }
    }

    /**
     * @param PhpNamespace $namespace
     * @param string $use
     * @param array|null $index
     * @param string $context
     * @return void
     * @static
     */
    public static function addUse(PhpNamespace $namespace, string $use, ?array $index = [], string $context = ''): void
    {
        if (!empty($index)) {
            foreach ($index as $class => $arguments) {
                str_replace($use, $use, $class, $count);

                if ($count
                    && (self::getClassNameFromNamespace($use) == self::getClassNameFromNamespace($class))
                ) {
                    $use = $class;
                }
            }
        }

        str_replace($context, $context, $use, $countContext);
        if ( !empty($context) && (0 == $countContext)) {
            $use = $context . '\\' . $use;
        }

        $namespace->addUse($use);
    }

    /**
     * @param ClassType $class
     * @param TemplaterInterface $templater
     * @param stdClass $data
     * @return void
     * @static
     */
    public static function setClassCommentor(ClassType $class, TemplaterInterface $templater, stdClass $data): void
    {
        $package = ucfirst(strtolower($templater->getCategory()));

        $class->addComment('Class ' . $templater->getTargetClassname());

        self::addComments($class, $data);

        $class->addComment('');
        $class->addComment('@category ' . $templater->getNamespace());
        $class->addComment('@package ' . $package);
        $class->addComment('@subpackage ' . str_replace($templater->getNamespace() . '\\' . $package . '\\', '', $templater->getTargetNamespace()));
        $class->addComment('@author SFYNX <contact@pi-groupe.net>');
        $class->addComment('@licence LGPL');
    }

    /**
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @param stdClass $data
     * @param array|null $index
     * @param string $context
     * @return void
     * @static
     */
    public static function setExtends(PhpNamespace $namespace, ClassType $class, stdClass $data, ?array $index = [], string $context = ''): void
    {
        if (property_exists($data, 'extends')
            && $data->extends
        ) {
            $class->setExtends(self::getClassNameFromNamespace($data->extends));
            self::addUse($namespace, $data->extends, $index, $context);
        }
    }

    /**
     * @param ClassType $class
     * @param stdClass $data
     * @return void
     * @static
     */
    public static function addComments(ClassType $class, stdClass $data): void
    {
        if (property_exists($data, 'comments')
            && $data->comments
        ) {
            foreach ($data->comments as $comment) {
                $class->addComment($comment);
            }
        }
    }

    /**
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @param stdClass $data
     * @param array|null $index
     * @param string $context
     * @return void
     * @static
     */
    public static function addImplements(PhpNamespace $namespace, ClassType $class, stdClass $data, ?array $index = [], string $context = ''): void
    {
        if (property_exists($data, 'options')
            && property_exists($data->options, 'implements')
            && $data->options->implements
        ) {
            foreach ($data->options->implements as $implement) {
                $class->addImplement(self::getClassNameFromNamespace($implement));
                self::addUse($namespace, $implement, $index, $context);
            }
        }
    }

    /**
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @param stdClass $data
     * @param array|null $index
     * @param string $context
     * @return void
     * @static
     */
    public static function addTraits(PhpNamespace $namespace, ClassType $class, stdClass $data, ?array $index = [], string $context = ''): void
    {
        if (property_exists($data, 'options')
            && property_exists($data->options, 'traits')
            && $data->options->traits
        ) {
            foreach ($data->options->traits as $trait) {
                $class->addTrait(self::getClassNameFromNamespace($trait));
                self::addUse($namespace, $trait, $index, $context);
            }
        }
    }

    /**
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @param stdClass $data
     * @param array|null $index
     * @return void
     * @static
     */
    public static function addMethods(PhpNamespace $namespace, ClassType $class, stdClass $data, ?array $index = []): void
    {
        if (property_exists($data, 'options')
            && property_exists($data->options, 'methods')
            && $data->options->methods
        ) {
            foreach ($data->options->methods as $name => $method) {
                if (property_exists($method, 'name')) {
                    // set default values
                    $methodArgs = '';
                    $body = '';

                    // create method
                    $methodClass = $class->addMethod($method->name);

                    // set Method values
                    if (property_exists($method, 'comments')) {
                        foreach ($method->comments as $comment) {
                            $methodClass->addComment($comment);
                        }
                    }
                    if (property_exists($method, 'arguments') && !empty($method->arguments)) {
                        $methodArgs = self::setArgs($namespace, $method->arguments, $index, false);

                        foreach ($method->arguments as $argument) {
                            $info = self::getArgResult($namespace, $argument, [], false);
                            if ('interface' == $info['type']) {
                                $methodClass->addParameter($info['value'])->setTypeHint($info['basename']);
                            }

                            $newArgument = trim(str_replace(' $', ' ', trim($argument), $countSpace));
                            if (1 == $countSpace) {
                                $info = explode(' ', $newArgument);
                                $type = $info[0];
                                $arg = $info[1];

                                str_replace('=', ' ', trim($argument), $countDefaultValue);
                                if (1 == $countDefaultValue) {
                                    $defaultValue = end($info);
                                    $defaultValue = ($defaultValue == 'false') ? false : $defaultValue;
                                    $defaultValue = ($defaultValue == 'true') ? true : $defaultValue;
                                    $methodClass->addParameter($arg)->setTypeHint($type)->setDefaultValue($defaultValue);
                                } else {
                                    $methodClass->addParameter($arg)->setTypeHint($type);
                                }
                            } else {
                                $newArgument = str_replace('$', '', trim($argument), $countSpace);
                                if (1 == $countSpace) {
                                    $methodClass->addParameter($newArgument);
                                }
                            }
                        }
                    }
                    if (property_exists($method, 'visibility')) {
                        $methodClass->setVisibility($method->visibility);
                    }
                    if (property_exists($method, 'returnType')) {
                        $info = self::getArgResult($namespace, $method->returnType, [], false);
                        $methodClass->setReturnType($info['basename']);
                    }

                    // set Body of the method

                    if (property_exists($method, 'body') && !empty($method->body)) {
                        foreach ($method->body as $line) {
                            $body .= $line . PHP_EOL;
                        }
                    }
                    if (property_exists($method, 'returnParent')) {
                        $body .= "return parent::$method->name($methodArgs)" . PHP_EOL;
                    }

                    $methodClass->addBody($body);
                }
            }
        }
    }

    /**
     * @param string $interfaceName
     * @param string $attributeName
     * @return void
     */
    public static function addConstructorArgument(string $interfaceName, string $attributeName): void
    {
        self::$constructorArguments[$interfaceName] = $attributeName;
    }

    /**
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @return Method|null
     * @static
     */
    public static function addConstructorMethod(PhpNamespace $namespace, ClassType $class): ?Method
    {
        if (!empty(self::$constructorArguments)) {
            $method = $class->addMethod('__construct')
                ->addComment(sprintf('%s constructor.', $class->getName()))
                ->addComment('');

            $body = '';
            foreach (self::$constructorArguments as $interface => $attribute) {
                $arg = lcfirst(str_replace('Interface', '', $interface));
                $body .= "$attribute = \$$arg;" . PHP_EOL;

                $method->addParameter($arg)->setTypeHint($interface);
                $method->addComment(sprintf('@param %s %s', $interface, $arg));

                $class->addProperty($arg)->setComment(sprintf('/** @var %s */', $interface))->setVisibility('protected');
            }

            $method->addBody($body);

            return  $method;
        }

        return null;
    }

    /**
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @return Method
     * @static
     */
    public static function addCoordinationMethod(PhpNamespace $namespace, ClassType $class): Method
    {
        $namespace->addUse('Symfony\Component\HttpFoundation\Response');

        return $class->addMethod('coordinate')->addComment('@return Response');
    }

    /**
     * @param PhpNamespace $namespace
     * @param array $arguments
     * @param array|null $index
     * @param bool $addConstruct
     * @return string
     */
    public static function setArgs(PhpNamespace $namespace, array $arguments, ?array $index = [], bool $addConstruct = true): string
    {
        $result = [];
        foreach ($arguments as $argument) {
            $info = self::getArgResult($namespace, $argument, $index, $addConstruct);
            $result[] = $info['argument'];
        }

        return implode(', ', $result);
    }

    /**
     * @param PhpNamespace $namespace
     * @param string $argument
     * @param array|null $index
     * @param bool $addConstruct
     * @return array
     */
    public static function getArgResult(PhpNamespace $namespace, string $argument, ?array $index = [], bool $addConstruct = true): array
    {
        $basename = ClassHandler::getClassNameFromNamespace($argument);
        $argResult = $argument;
        $type = 'default';
        $value = $argument;

        $className = trim(str_replace('new', '', $argument, $countNew));
        if (1 == $countNew) {
            $argResult = self::setArgNewResult($namespace, $argument, $index, $className);
            $type = 'new';
            $value = $className;
        }

        $interfaceName = lcfirst(str_replace('Interface', '', $basename, $countInterface));
        if (1 == $countInterface) {
            $argResult = self::setArgInterfaceResult($namespace, $argument, $index, $interfaceName, $basename, $addConstruct);
            $type = 'interface';
            $value = $interfaceName;
        }

        $newArgument = str_replace(' ', ' ', trim($argument), $countVar);
        if ((1 <= $countVar) && (0 == $countNew)) {
            $argResult = self::setArgVarResult($newArgument);
            $type = 'var';
            $value = $newArgument;
        }

        return ['argument' => $argResult, 'basename' => $basename, 'type' => $type, 'value' => $value];
    }

    /**
     * @param PhpNamespace $namespace
     * @param string $argument
     * @param array|null $index
     * @param string $className
     * @return string
     */
    public static function setArgNewResult(PhpNamespace $namespace, string $argument, ?array $index = [], string $className): string
    {
        $newArgs = null;

        foreach ($index as $class => $args) {
            str_replace($className, $className, $class, $countClass);

            if (($countClass == 1)
                && !empty($args)
            ) {
                foreach ($args as $arg) {
                    $info = self::getArgResult($namespace, $arg, $index);
                    $newArgs[] = $info['argument'];
                }
                $newArgs = implode(', ', $newArgs);
            }
        }

        self::addUse($namespace, $className, $index);

        return "$argument($newArgs)";
    }

    /**
     * @param PhpNamespace $namespace
     * @param string $argument
     * @param array|null $index
     * @param string $interfaceName
     * @param string $basename
     * @param bool $addConstruct
     * @return string
     */
    public static function setArgInterfaceResult(PhpNamespace $namespace, string $argument, ?array $index = [], string $interfaceName, string $basename, bool $addConstruct = true): string
    {
        self::addUse($namespace, $argument, $index);

        $attribute = "\$$interfaceName";
        if ($addConstruct) {
            $attribute = "\$this->$interfaceName";
            self::addConstructorArgument($basename, $attribute);
        }

        return "$attribute";
    }

    /**
     * @param string $argument
     * @return string
     */
    public static function setArgVarResult(string $argument): string
    {
        list($type, $arg) = explode(' ', $argument);

        return $arg;
    }
}