<?php
namespace Sfynx\CoreBundle\Layers\Infrastructure\Persistence\Adapter\Generalisation\Orm\Traits;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Types\Type;
use Gedmo\Translatable\TranslatableListener;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Translatable\Mapping\Event\Adapter\ORM as TranslatableAdapterORM;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Translation Repository
 *
 * @category Sfynx\CoreBundle
 * @package Infrastructure
 * @subpackage Persistence\Generalisation\Orm\Traits
 *
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2015 PI-GROUPE
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    2.3
 * @link       http://opensource.org/licenses/gpl-license.php
 * @since      2015-02-17
 */
trait TraitTranslation
{
    use TraitGeneral;

    /**
     * Current TranslatableListener instance used
     * in EntityManager
     *
     * @var TranslatableListener
     */
    private $listener;

    /**
     * Value of the  associated translation class.
     *
     * @var string
     */
    private $_entityTranslationName = "";

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);

        if (isset($this->getClassMetadata()->associationMappings['translations'])
            && !empty($this->getClassMetadata()->associationMappings['translations'])
        ) {
           $this->_entityTranslationName = $this->getClassMetadata()
                   ->associationMappings['translations']['targetEntity'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findOneQueryByEntity($id)
    {
        return $this->createQueryBuilder('a')
        ->select('a')
        ->where('a.id = :ID')
           ->setParameters([
                'ID'    => $id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findTranslations($entity)
    {
        $result = [];
        $wrapped = new EntityWrapper($entity, $this->_em);
        if ($wrapped->hasValidIdentifier()) {
            $entityId = $wrapped->getIdentifier();
            $entityClass = $wrapped->getMetadata()->name;

            $translationMeta = $this->getClassMetadata(); // table inheritance support
            $qb = $this->_em->createQueryBuilder();
            $qb->select('trans.content, trans.field, trans.locale')
            ->from($translationMeta->associationMappings['translations']['targetEntity'], 'trans')
            ->where('trans.object = :entityId')
            ->orderBy('trans.locale');
            $q = $qb->getQuery();
            $data = $q->execute(
                compact('entityId', 'entityId'),
                Query::HYDRATE_ARRAY
            );

            if ($data
                    && \is_array($data)
                    && \count($data)
            ) {
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']][] = $row['content'];
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findTranslationsByObjectId($id)
    {
        $result = [];
        if ($id) {
            $translationMeta = $this->getClassMetadata(); // table inheritance support
            // $this->_entityTranslationName
            $qb = $this->_em->createQueryBuilder();
            $qb->select('trans.content, trans.field, trans.locale')
            ->from($translationMeta->associationMappings['translations']['targetEntity'], 'trans')
            ->where('trans.object = :entityId')
            ->orderBy('trans.locale');
            $q = $qb->getQuery();
            $data = $q->execute(
                array('entityId' => $id),
                Query::HYDRATE_ARRAY
            );

            if ($data
                    && \is_array($data)
                    && \count($data)
             ) {
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']] = $row['content'];
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($entity, $field, $locale, $value)
    {
        $meta = $this->_em->getClassMetadata(\get_class($entity));
        $listener = new \Gedmo\Translatable\TranslatableListener; //$this->getTranslatableListener();
        $config = $listener->getConfiguration($this->_em, $meta->name);
        if (!isset($config['fields']) || !\in_array($field, $config['fields'])) {
            throw new \Gedmo\Exception\InvalidArgumentException("Entity: {$meta->name} does not translate field - {$field}");
        }
        if (\in_array($locale, array($listener->getDefaultLocale(), $listener->getTranslatableLocale($entity, $meta)))) {
            $meta->getReflectionProperty($field)->setValue($entity, $value);
            $this->persist($entity, false);
        } else {
            $ea = new TranslatableAdapterORM();
            $foreignKey = $meta->getReflectionProperty($meta->getSingleIdentifierFieldName())->getValue($entity);
            $objectClass = $meta->name;
            $class = $listener->getTranslationClass($ea, $meta->name);
            $transMeta = $this->_em->getClassMetadata($class);
            $trans = $this->findOneBy(compact('locale', 'field', 'object'));
            if (!$trans) {
                $trans = new $class();
                $transMeta->getReflectionProperty('object')->setValue($trans, $entity->getId());
                $transMeta->getReflectionProperty('field')->setValue($trans, $field);
                $transMeta->getReflectionProperty('locale')->setValue($trans, $locale);
            }
            $type = Type::getType($meta->getTypeOfField($field));
            $transformed = $type->convertToDatabaseValue($value, $this->_em->getConnection()->getDatabasePlatform());
            $transMeta->getReflectionProperty('content')->setValue($trans, $transformed);
            if ($this->_em->getUnitOfWork()->isInIdentityMap($entity)) {
                $this->_em->persist($trans);
            } else {
                $oid = spl_object_hash($entity);
                $listener->addPendingTranslationInsert($oid, $trans);
            }
        }
        return $this;

//         $meta         = $this->_em->getClassMetadata(\get_class($entity));
//         $listener     = $this->getTranslatableListener();
//         $config     = $listener->getConfiguration($this->_em, $meta->name);

//         if (!isset($config['fields']) || !\in_array($field, $config['fields'])) {
//             throw new \Gedmo\Exception\InvalidArgumentException("Entity: {$meta->name} does not translate field - {$field}");
//         }

//         $ea         = new TranslatableAdapterORM();
//         $class         = $listener->getTranslationClass($ea, $meta->name);

//         $trans         = $this->findOneBy(compact('locale', 'field', 'object_id'));
//         if (!$trans) {
//             $entity->setTranslatableLocale('fr');
//             $entity->addTranslation(new $class($locale, $field, $value));
//         }

//         $this->_em->persist($entity);
//         $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectByTranslatedField($field, $value, $class)
    {
//         $entity = null;
//         $meta = $this->_em->getClassMetadata($class);
//         $translationMeta = $this->getClassMetadata(); // table inheritance support
//         if ($meta->hasField($field)) {
//             $dql = "SELECT trans.foreignKey FROM {$translationMeta->rootEntityName} trans";
//             $dql .= ' WHERE trans.objectClass = :class';
//             $dql .= ' AND trans.field = :field';
//             $dql .= ' AND trans.content = :value';
//             $q = $this->_em->createQuery($dql);
//             $q->setParameters(compact('class', 'field', 'value'));
//             $q->setMaxResults(1);
//             $result = $q->getArrayResult();
//             $id = \count($result) ? $result[0]['foreignKey'] : null;

//             if ($id) {
//                 $entity = $this->_em->find($class, $id);
//             }
//         }
//         return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayAllByField($field)
    {
        $query = $this->createQueryBuilder('a')
        ->select("a.{$field}")
        ->where('a.enabled = :enabled')
        ->andWhere('a.archived = :archived')
        ->setParameters(array(
            'enabled'  => 1,
            'archived' => 0,
        ));

        $result = array();
        $data   = $query->getQuery()->getArrayResult();
        if ($data && \is_array($data) && \count($data)) {
            foreach ($data as $row) {
                if (isset($row[$field]) && !empty($row[$field])) {
                    $result[ $row[$field] ] = $row[$field];
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByCategory($category = '', $MaxResults = null, $ORDER_PublishDate = '', $ORDER_Position = '', $enabled = true, $is_checkRoles = true, $with_archive = false)
    {
        $query = $this->createQueryBuilder('a')->select('a');
        if (!empty($ORDER_PublishDate) && !empty($ORDER_Position)) {
            $query
                ->orderBy('a.published_at', $ORDER_PublishDate)
                ->addOrderBy('a.position', $ORDER_Position);
        } elseif (!empty($ORDER_PublishDate) && empty($ORDER_Position)) {
            $query
                ->orderBy('a.published_at', $ORDER_PublishDate);
        } elseif (empty($ORDER_PublishDate) && !empty($ORDER_Position)) {
            $query
                ->orderBy('a.position', $ORDER_Position);
        }
        if (!$with_archive) {
            $query->where('a.archived = 0');
        }
        if ($enabled && !empty($category)) {
            $query
            ->andWhere('a.enabled = :enabled')
            ->andWhere('a.category = :cat')
            ->setParameters(array(
                    'cat'        => $category,
                    'enabled'    => 1,
            ));
        } elseif ($enabled && empty($category)) {
            $query
            ->andWhere('a.enabled = :enabled')
            ->setParameters(array(
                    'enabled'    => 1,
            ));
        } elseif (!$enabled && !empty($category)) {
            $query
            ->andWhere('a.category = :cat')
            ->setParameters(array(
                    'cat'        => $category,
            ));
        }
        if (!(null === $MaxResults)) {
            $query->setMaxResults($MaxResults);
        }
//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByFields($fields = array(), $MaxResults = null, $ORDER_PublishDate = '', $ORDER_Position = '', $is_checkRoles = true)
    {
        $query = $this->createQueryBuilder('a')->select('a');
        if (!empty($ORDER_PublishDate) && !empty($ORDER_Position)) {
            $query
            ->orderBy('a.published_at', $ORDER_PublishDate)
            ->addOrderBy('a.position', $ORDER_Position);
        } elseif (!empty($ORDER_PublishDate) && empty($ORDER_Position)) {
            $query
            ->orderBy('a.published_at', $ORDER_PublishDate);
        } elseif (empty($ORDER_PublishDate) && !empty($ORDER_Position)) {
            $query
            ->orderBy('a.position', $ORDER_Position);
        }
        foreach ($fields as $key => $value) {
            if (\is_int($value)) {
                $query->andWhere("a.{$key} = $value");
            } else {
                $query->andWhere("a.{$key} LIKE '{$value}'");
            }
        }
        if (!(null === $MaxResults)) {
            $query->setMaxResults($MaxResults);
        }

//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOrderByField($field = 'createat', $ORDER = "DESC", $enabled = null, $is_checkRoles = true, $with_archive = false)
    {
        $query = $this->createQueryBuilder('a')
        ->select("a");
        if (!$with_archive){
            $query->where('a.archived = 0');
        }
        if ( !(null === $enabled) ) {
            $query
            ->andWhere('a.enabled = :enabled')
            ->setParameters(array(
                    'enabled'    => $enabled,
            ));
        }
        $query->orderBy("a.{$field}", $ORDER);

//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllBetweenPosition($FirstPosition = null, $LastPosition = null, $enabled = null, $is_checkRoles = true, $with_archive = false)
    {
        $query = $this->createQueryBuilder('a')
        ->select("a");
        if (!$with_archive){
            $query->where('a.archived = 0');
        }
        if (!(null === $FirstPosition) && !(null === $LastPosition)) {
            $query
            ->andWhere("a.position BETWEEN '{$FirstPosition}' AND '{$LastPosition}'");
        } elseif (!(null === $FirstPosition) && (null === $LastPosition)) {
            $query
            ->andWhere("a.position >= {$FirstPosition} ");
        } elseif ((null === $FirstPosition) && !(null === $LastPosition)) {
            $query
            ->andWhere("a.position <= {$LastPosition} ");
        }
        if (!(null === $enabled)) {
            $query
            ->andWhere('a.enabled = :enabled')
            ->setParameters(array(
                    'enabled'    => $enabled,
            ));
        }
        $query->orderBy("a.position", 'ASC');

//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxOrMinValueOfColumn($field, $type = 'MAX', $enabled = null, $is_checkRoles = true, $with_archive = false)
    {
        $query = $this->createQueryBuilder('a')->select("a.{$field}");
        if (!$with_archive){
        	$query->where('a.archived = 0');
        }
        if ($type == "MAX") {
            $query->orderBy("a.{$field}", 'DESC');
        } elseif ($type == "MIN") {
            $query->orderBy("a.{$field}", 'ASC');
        }
        if (!(null === $enabled)) {
            $query
            ->andWhere('a.enabled = :enabled')
            ->setParameters(array(
                    'enabled'    => $enabled,
            ));
        }
        $query->setMaxResults(1);

//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEnabled($locale, $result = "object", $INNER_JOIN = false, $MaxResults = null, $is_checkRoles = true, $FALLBACK = true, $lazy_loading = true)
    {
        $query = $this->_em->createQueryBuilder()
        ->select('a')
        ->from($this->_entityName, 'a')
        ->where('a.archived = 0')
        ->andWhere('a.enabled = 1')
        ->setMaxResults($MaxResults);

//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $this->findTranslationsByQuery($locale, $query->getQuery(), $result, $INNER_JOIN, $FALLBACK, $lazy_loading);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEnableByCat($locale, $category, $result = "object", $INNER_JOIN = false, $is_checkRoles = true, $FALLBACK = true, $lazy_loading = true)
    {
        $query = $this->_em->createQueryBuilder()
        ->select('a')
        ->from($this->_entityName, 'a')
        ->where('a.archived = 0')
        ->andWhere("a.enabled = 1");
        if (!empty($category)) {
            $query
            ->andWhere('a.category = :cat')
            ->setParameters(array(
                    'cat' => $category,
            ));
        }
//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $this->findTranslationsByQuery($locale, $query->getQuery(), $result, $INNER_JOIN, $FALLBACK, $lazy_loading);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEnableByCatAndByPosition($locale, $category, $result = "object", $INNER_JOIN = false, $is_checkRoles = true, $FALLBACK = true, $lazy_loading = true)
    {
        $query = $this->_em->createQueryBuilder()
        ->select('a')
        ->from($this->_entityName, 'a')
        ->orderBy('a.position', 'ASC')
        ->where('a.archived = 0')
        ->andWhere("a.enabled = 1");
        if (!empty($category)) {
            $query
            ->andWhere('a.category = :cat')
            ->setParameters(array(
                'cat' => $category,
            ));
        }
//        if ($is_checkRoles) {
//            $query = $this->checkRoles($query);
//        }

        return $this->findTranslationsByQuery($locale, $query->getQuery(), $result, $INNER_JOIN, $FALLBACK, $lazy_loading);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentByField($locale, array $fields, $INNER_JOIN = false)
    {
        $query = $this->_em->createQuery("SELECT p FROM {$this->_entityTranslationName} p  WHERE p.locale = :locale and p.field = :field and p.content = :content ");
        $query->setParameter('locale', $locale);
        $query->setParameter('field', array_keys($fields['content_search']));
        $query->setParameter('content', array_values($fields['content_search']));
        $query->setMaxResults(1);
        $entities = $query->getResult();
        if (!(null === $entities)) {
            $entity = current($entities);
            if (\is_object($entity)) {
                $id    = $entity->getObject()->getId();
                $query = $this->_em->createQuery("SELECT p FROM {$this->_entityTranslationName} p  WHERE p.locale = :locale and p.field = :field and p.object = :objectId");
                $query->setParameter('locale', $locale);
                $query->setParameter('objectId', $id);
                $query->setParameter('field', $fields['field_result']);
                $query->setMaxResults(1);
                $entities = $query->getResult();
                if (!(null === $entities) && (\count($entities)>=1) ) {
                    return current($entities);
                }
                return null;
            }
            return null;
        }
        return null;

        //         $dql = <<<___SQL
        //   SELECT a
        //   FROM {$this->_entityName} a
        //   WHERE a.slug = '{$slug}'
        // ___SQL;

        //         $query  = $this->_em->createQuery($dql);
        //         $result = $this->findTranslationsByQuery($locale, $query, $result, $INNER_JOIN);


        //         print_r(\count($result));exit;

        //         return current($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityByField($locale, array $fields, $result = "object", $INNER_JOIN = false)
    {
        $query = $this->_em->createQuery("SELECT p FROM {$this->_entityTranslationName} p  WHERE p.locale = :locale and p.field = :field and p.content = :content ");
        $query->setParameter('locale', $locale);
        $query->setParameter('field', array_keys($fields['content_search']));
        $query->setParameter('content', array_values($fields['content_search']));
        $query->setMaxResults(1);
        $entities = $query->getResult();
        if (!(null === $entities)) {
            $entity = current($entities);
            if (\is_object($entity)) {
                $id = $entity->getObject()->getId();
                return $this->findOneByEntity($locale, $id, $result, $INNER_JOIN);
            }
            return null;
        }
        return null;
    }
}
