<?php
/**
 * 
 */
namespace Application\Model;

use Application\Entity\Utils\EntitySerializer;

class ModelResult
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var Application\Entity\Utils\EntitySerializer
     */
    private $entitySerializer;

    public function __construct($data, $entityManager)
    {
        $this->data = $data;
        $this->entityManager = $entityManager;
    }

    public function toRaw()
    {
        return $this->data;
    }

    public function toArray()
    {
        return $this->getEntitySerializer()->toArray($this->toRaw());
    }

    public function toJson()
    {
        return $this->getEntitySerializer()->toJson($this->toRaw());
    }

    public function toXml()
    {
        return $this->getEntitySerializer()->toXml($this->toRaw());
    }

    /**
     * Returns the EntitySerializer
     *
     * @return Application\Entity\Utils\EntitySerializer
     */
    protected function getEntitySerializer()
    {
        if ($this->entitySerializer == null) {
            $this->entitySerializer = new EntitySerializer($this->entityManager);
        }
        return $this->entitySerializer;
    }
}
