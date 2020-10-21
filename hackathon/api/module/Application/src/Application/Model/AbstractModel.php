<?php
/**
 * 
 */
namespace Application\Model;

use Doctrine\ORM\EntityManager;
use Application\Model\ModelResult;

class AbstractModel
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Sets the EntityManager
     *
     * @param EntityManager $em
     * @access protected
     * @return PostController
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->entityManager = $em;
        return $this;
    }

    /**
     * Returns the EntityManager
     *
     * Fetches the EntityManager from ServiceLocator if it has not been initiated
     * and then returns it
     *
     * @access protected
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param string $entityClass
     * @return array
     */
    public function getList($entityClass, $param = array(), $order = null)
    {
    	$entityClass = $entityClass ? $entityClass : $this->getEntityClass();
    	return $this->getEntityManager()->getRepository($entityClass)->findBy($param, $order);
    }
    
    /**
     * @param array $param
     * @param string $entityClass
     * @return array
     */
    public function getBy($param, $entityClass = null, $order = null, $limit = null, $offset = null)
    {
    	 
    	$entityClass = $entityClass ? $entityClass : $this->getEntityClass();
    	return $this->getEntityManager()->getRepository($entityClass)->findBy($param, $order, $limit, $offset);
    }

    /**
     * @param object $entityClass
     * @param int $id
     * @return object
     */
    public function get($entityClass, $id)
    {
        return $this->getEntityManager()->find($entityClass, $id);
    }

    /**
     * @param  object $entity
     * @return object
     */
    public function save($entity)
    {
    	$this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    /**
     * @param string $entityClass
     * @param int $id
     */
    public function delete($entityClass, $id)
    {
        $entity = $this->getEntityManager()->find($entityClass, $id);
        
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param mixed $data
     */
    public function transform($data)
    {
        return new ModelResult($data, $this->getEntityManager());
    }
}
