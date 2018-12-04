<?php
namespace Sfynx\CoreBundle\Generator\Domain\Templater\Templater_\Architecture\Domain\Entity;

use Sfynx\CoreBundle\Generator\Domain\Widget\Generalisation\Interfaces\WidgetInterface;
use Sfynx\CoreBundle\Generator\Domain\Templater\Generalisation\Interfaces\TemplaterInterface;
use Sfynx\CoreBundle\Generator\Domain\Templater\Generalisation\AbstractTemplater;
use Sfynx\CoreBundle\Generator\Domain\Report\ReporterObservable;

/**
 * @category   Sfynx\CoreBundle\Generator
 * @package    Domain
 * @subpackage TemplaterTemplater_\Architecture\Domain\Entity
 *
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class Templater extends AbstractTemplater implements TemplaterInterface
{
    /** @var string */
    const TAG = 'templater_archi_dom_entity';

    /** @var array */
    const TARGET_ATTRIBUTS = [
        'conf-mapping' => 'all',
        'conf-options' => 'options',
        'conf-widget',
        'conf-cqrs'
    ];

    /** @var string */
    const TEMPLATE_GENERATOR = ReporterObservable::GENERATOR_PHP_MULTIPLE;

    /**
     * @inheritdoc
     */
    public static function scriptList(string $template): array
    {
        return ['Domain'];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Entity class';
    }

    /**
     * @inheritdoc
     */
    public function getCategory(): string
    {
        return WidgetInterface::CAT_ARCHI_DOM;
    }

    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return self::TAG;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return <<<EOT
This class expose entity component
EOT;
    }
}

