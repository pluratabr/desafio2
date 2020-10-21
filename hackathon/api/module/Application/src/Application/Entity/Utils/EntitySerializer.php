<?php
/**
 * 
 */

namespace Application\Entity\Utils;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityManager;

class EntitySerializer
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var int
     */
    protected $recursionDepth = 0;

    /**
     * @var int
     */
    protected $maxRecursionDepth = 0;

    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    protected function serializeEntity($entity)
    {
        if (!$entity) {
            return null;
        }
        $className = get_class($entity);
        $metadata = $this->entityManager->getClassMetadata($className);

        $data = array();

        foreach ($metadata->fieldMappings as $field => $mapping) {
            $value = $metadata->reflFields[$field]->getValue($entity);
            $field = Inflector::tableize($field);
            if ($value instanceof \DateTime) {
                // We cast DateTime to array to keep consistency with array result
                $data[$field] = (array)$value;
            } elseif (is_object($value)) {
                $data[$field] = (string)$value;
            } else {
                $data[$field] = $value;
            }
        }

        foreach ($metadata->associationMappings as $field => $mapping) {
            $key = Inflector::tableize($field);
            if ($mapping['isCascadeDetach']) {
                $data[$key] = $metadata->reflFields[$field]->getValue($entity);
                if (null !== $data[$key]) {
                    $data[$key] = $this->serializeEntity($data[$key]);
                }
            } elseif ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE) {
                if (null !== $metadata->reflFields[$field]->getValue($entity)) {
                    if ($this->recursionDepth < $this->maxRecursionDepth) {
                        $this->recursionDepth++;
                        $data[$key] = $this->serializeEntity(
                            $metadata->reflFields[$field]->getValue($entity)
                        );
                        $this->recursionDepth--;
                    } else {
                        $data[$key] = $this->getEntityManager()
                            ->getUnitOfWork()
                            ->getEntityIdentifier(
                                $metadata->reflFields[$field]->getValue($entity)
                            );
                    }
                } else {
                    // In some case the relationship may not exist, but we want
                    // to know about it
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Serialize an entity to an array
     * Modified for me
     *
     * @param The entity $entity
     * @return array
     */
    public function toArray($entities)
    {
        $serialized = array();
        if (is_array($entities)) {
            foreach ($entities as $entity) {
                array_push($serialized, $this->serializeEntity($entity));
            }
        } else {
            $serialized = $this->serializeEntity($entities);
        }
        return $serialized;
    }


    /**
     * Convert an entity to a JSON object
     *
     * @param The entity $entity
     * @return string
     */
    public function toJson($entity)
    {
        return json_encode($this->toArray($entity));
    }

    /**
     * Convert an entity to XML representation
     *
     * @param The entity $entity
     * @throws \Exception
     */
    public function toXml($entity)
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Set the maximum recursion depth
     *
     * @param int $maxRecursionDepth
     * @return void
     */
    public function setMaxRecursionDepth($maxRecursionDepth)
    {
        $this->maxRecursionDepth = $maxRecursionDepth;
    }

    /**
     * Get the maximum recursion depth
     *
     * @return int
     */
    public function getMaxRecursionDepth()
    {
        return $this->maxRecursionDepth;
    }
}
