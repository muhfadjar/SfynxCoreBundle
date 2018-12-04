<?php
namespace Sfynx\CoreBundle\Layers\Application\Query;

use Sfynx\CoreBundle\Layers\Application\Query\Generalisation\AbstractQuery;

/**
 * Class IndexQuery.
 *
 * @category   Sfynx\CoreBundle\Layers
 * @package    Application
 * @subpackage Query
 */
class IndexQuery extends AbstractQuery
{
    /** @var string */
    protected $locale;
    /** @var string */
    protected $category = '';
    /** @var boolean */
    protected $noLayout = false;
    /** @var boolean */
    public $isServerSide = false;
    /** @var int */
    protected $sEcho = '';
}
