namespace <?php echo $templater->getTargetNamespace(); ?>;

use Symfony\Component\OptionsResolver\Options;
use Sfynx\CoreBundle\Layers\Presentation\Request\Generalisation\AbstractFormRequest;

/**
 * Class <?php echo $templater->getTargetClassname(); ?><?php echo PHP_EOL ?>
 *
 * @category <?php echo $templater->getNamespace(); ?><?php echo PHP_EOL ?>
 * @package Presentation
 * @subpackage <?php echo str_replace($templater->getNamespace() . '\Presentation\\', '', $templater->getTargetNamespace()); ?><?php echo PHP_EOL ?>
 * @author SFYNX <contact@pi-groupe.net><?php echo PHP_EOL ?>
 * @licence LGPL
 */
class <?php echo $templater->getTargetClassname(); ?> extends AbstractFormRequest
{
    /**
     * @var array $defaults List of default values for optional parameters.
     */
    protected $defaults = [
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
        '<?php echo $field->name ?>' => null,
<?php endforeach; ?>
    ];

    /**
     * @var string[] $required List of required parameters for each methods.
     */
    protected $required = [
        'GET'  => [
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
<?php if ($field->name != 'entityId'): ?>
            '<?php echo $field->name ?>',
<?php endif; ?>
<?php endforeach; ?>
        ],
        'POST'  => [
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
            '<?php echo $field->name ?>',
<?php endforeach; ?>
        ],
        'PATCH' => 'POST'
    ];

    /**
     * @var array[] $allowedTypes List of allowed types for each methods.
     */
    protected $allowedTypes = [
        'GET' => [
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
<?php if ($field->name != 'entityId'): ?>
            '<?php echo $field->name ?>' => ['<?php if (strpos($field->type, 'entityId') !== false): ?>integer<?php elseif (strpos(strtolower($field->type), 'id') !== false): ?>integer<?php elseif (strtolower($field->type) == 'number'): ?>integer<?php elseif (strtolower($field->type) == 'datetime'): ?>DateTime<?php elseif (strtolower($field->type) == 'valueobject'): ?>array<?php else: ?><?php echo $field->type ?><?php endif; ?>', 'null'],
<?php endif; ?>
<?php endforeach; ?>
        ],
        'POST' => [
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
            '<?php echo $field->name ?>' => ['<?php if (strpos($field->type, 'entityId') !== false): ?>integer<?php elseif (strpos(strtolower($field->type), 'id') !== false): ?>integer<?php elseif (strtolower($field->type) == 'number'): ?>integer<?php elseif (strtolower($field->type) == 'datetime'): ?>DateTime<?php elseif (strtolower($field->type) == 'valueobject'): ?>array<?php else: ?><?php echo $field->type ?><?php endif; ?>', 'null'],
<?php endforeach; ?>
        ],
        'PATCH' => 'POST'
    ];

    /**
     * @return void
     */
    protected function setOptions()
    {
        $this->options = $this->request->getRequest()->get('', []);

        // boolean trsnaformation
        $dataBool = [
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
<?php if (strpos(strtolower($field->type), 'bool') !== false): ?>
        <?php echo $field->name ?>
<?php endif; ?>
<?php endforeach; ?>
        ];

        foreach ($dataBool as $data) {
            if (isset($this->options[$data])) {
                $this->options[$data] = (int)$this->options[$data] ? true : false;
            }
        }

        // identifier trsnaformation
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
<?php if (strpos(strtolower($field->type), 'id') !== false): ?>
        $<?php echo $field->name ?> = $this->request->get('<?php echo $field->name ?>', '');
        $this->options['<?php echo $field->name ?>'] = ('' !== $<?php echo $field->name ?>) ? (int)$<?php echo $field->name ?> : null;

<?php endif; ?>
<?php endforeach; ?>

        // datetime trsnaformation
<?php foreach ($templater->getTargetCommandFields() as $field): ?>
<?php if (strpos(strtolower($field->type), 'datetime') !== false): ?>
        if (isset($this->options['<?php echo $field->name ?>'])) {
            $data = $this->options['<?php echo $field->name ?>'];
            $this->options['<?php echo $field->name ?>'] = (null !== $data && !empty($data)) ? new \DateTime($data) : null;
        }
<?php endif; ?>
<?php endforeach; ?>

        $this->options = (null !== $this->options) ? $this->options : [];
    }
}