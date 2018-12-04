<?php
namespace Sfynx\CoreBundle\Generator\Domain\Component\File\MethodModel;

use stdClass;
use SplSubject;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Sfynx\CoreBundle\Generator\Domain\Component\File\ClassHandler;
use Sfynx\CoreBundle\Generator\Domain\Report\Generalisation\AbstractGenerator;

/**
 * Class Setter
 * @category   Sfynx\CoreBundle\Generator
 * @package    Domain
 * @subpackage Component\File\MethodModel
 *
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class Setter
{
    /**
     * Create setter method of a field
     *
     * @param PhpNamespace $namespace
     * @param ClassType $class
     * @param array|null $index
     * @param stdClass $field
     * @param string $typeFieldName
     * @param string $propertyFieldName
     * @static
     * @return void
     */
    public static function handle(
        PhpNamespace $namespace,
        ClassType $class,
        ?array $index = [],
        stdClass $field,
        string $typeFieldName = '',
        string $propertyFieldName = ''
    ): void {
        $setterFieldName = 'set' . \ucfirst($field->name);
        $ClassTypeFieldName = ClassHandler::getClassNameFromNamespace($typeFieldName);

        \str_replace('entityid', 'entityid', \strtolower($field->name), $isFieldEntity);
        if ($isFieldEntity) {
            $propertyFieldName = 'id';
            $ClassTypeFieldName = 'int';
            $typeFieldName = 'int';
            $getterFieldName = 'getId';
        }
        $setterFieldBody = \sprintf('$this->%s = $%s;', $propertyFieldName, $propertyFieldName) . PHP_EOL;
        $setterFieldBody .= \sprintf('return $this;') . PHP_EOL;

        $comment = \sprintf('@var %s $%s', $ClassTypeFieldName, $propertyFieldName);
        if (empty($typeFieldName)) {
            $comment = \sprintf('@var %s', $propertyFieldName);
        }
        ClassHandler::addUse($namespace, $typeFieldName, $index);

        $class->addProperty($propertyFieldName)
            ->setVisibility('protected')
            ->addComment($comment);

        ClassHandler::createMethods(
            $namespace,
            $class,
            AbstractGenerator::transform([
                'options' => [
                    'methods' => [[
                        'name' => $setterFieldName,
                        'comments' => ['Set ' . $propertyFieldName],
                        'visibility' => 'public',
                        'arguments' => [sprintf('%s $%s', $ClassTypeFieldName, $propertyFieldName)],
                        'returnType' => 'self',
                        'body' => [$setterFieldBody]
                    ]]
                ]
            ], false),
            $index
        );
    }
}
