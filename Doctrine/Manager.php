<?php

namespace Msi\AdminBundle\Doctrine;

use Doctrine\ORM\EntityManager;
use Msi\AdminBundle\Tools\ClassAnalyzer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Manager
{
    protected $em;
    protected $repository;
    protected $class;
    protected $classAnalyzer;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function update($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function updateBatch($entity, $i, $batchSize = 20)
    {
        $this->em->persist($entity);
        if ($i % $batchSize === 0) {
            $this->em->flush();
            $this->em->clear();
        }
    }

    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function toggle($entity, $field, $locale)
    {
        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        if (!$this->classAnalyzer->hasMethod($this->getMetadata()->reflClass, $getter)) {
            $entity->getTranslation($locale)->$getter() ? $entity->getTranslation($locale)->$setter(false) : $entity->getTranslation($locale)->$setter(true);
        } else {
            $entity->$getter() ? $entity->$setter(false) : $entity->$setter(true);
        }

        $this->update($entity);
    }

    public function moveUp($entity, $number = 1)
    {
        $this->repository->moveUp($entity, $number);
        $this->update($entity);
    }

    public function moveDown($entity, $number)
    {
        $this->repository->moveDown($entity, $number);
        $this->update($entity);
    }

    public function create()
    {
        return new $this->class();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getMetadata()
    {
        return $this->em->getClassMetadata($this->class);
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($this->class);

        return $this;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function getClassAnalyzer()
    {
        return $this->classAnalyzer;
    }

    public function setClassAnalyzer(ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;

        return $this;
    }

    public function findOrCreate($locale, array $where = [], array $join = [], array $orderBy = [], $throw = true)
    {
        if (isset($where['a.id'])) {
            $object = $this->find($where, $join, $orderBy, $throw);
        } else {
            $object = $this->create();
        }

        if ($this->classAnalyzer->hasTrait($this->getMetadata()->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Translatable')) {
            // need to remove the translations that we aren't working with or else they will be in the form and cause errors
            foreach ($object->getTranslations() as $key => $value) {
                if ($value->getLocale() !== $locale) {
                    $object->getTranslations()->remove($key);
                }
            }
            // here if our object doesn't have a translation for the working locale we create it
            if (!$object->hasTranslation($locale)) $object->createTranslation($locale);
        }

        return $object;
    }

    public function getOneBy(array $where, array $join = [], $throw = true)
    {
        $result = $this->getFindByQueryBuilder($where, $join)->getQuery()->getOneOrNullResult();
        if ($throw && !$result) {
            throw new NotFoundHttpException('getOneBy method says: '.$this->class.' was not found');
        }

        return $result;
    }

    public function find(array $where = [], array $join = [], array $orderBy = [], $throw = true)
    {
        $row = $this->getMasterQueryBuilder($where, $join, $orderBy)->getQuery()->getOneOrNullResult();

        if (!$row && $throw) {
            throw new NotFoundHttpException($this->getClass().' where '.http_build_query($where).' not found.');
        }

        return $row;
    }

    public function findAll(array $where = [], array $join = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $q = $this->getMasterQueryBuilder($where, $join, $orderBy, $limit, $offset)->getQuery();

        if (null !== $limit) {
            $results = (new Paginator($q))->getIterator()->getArrayCopy();
        } else {
            $results = $q->execute();
        }

        return $results;
    }

    public function getMasterQueryBuilder(array $where = [], array $join = [], array $orderBy = [], $limit = null, $offset = null)
    {
        return $this->getFindByQueryBuilder($where, $join, $orderBy, $limit, $offset);
    }

    public function getFindByQueryBuilder(array $where = [], array $join = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $qb = $this->repository->createQueryBuilder('a');

        $qb = $this->buildFindBy($qb, $where, $join, $orderBy);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }

    public function getSearchQueryBuilder($q, array $searchFields, array $where = [], array $join = [], array $orderBy = [], $explode = true)
    {
        $qb = $this->repository->createQueryBuilder('a');

        if (count($searchFields)) {
            $q = trim($q);
            // $strings = $explode ? explode(' ', $q) : [$q];
            $strings = [$q];

            $orX = $qb->expr()->orX();
            $i = 1;
            foreach ($searchFields as $field) {
                foreach ($strings as $string) {
                    $token = 'likeMatch'.$i;
                    $orX->add($qb->expr()->like($field, ':'.$token));
                    $qb->setParameter($token, '%'.$string.'%');

                    $orX->add($qb->expr()->like('a.id', ':eqMatchForId'.$i));
                    $qb->setParameter('eqMatchForId'.$i, $string);
                    $i++;
                }
            }

            $qb->andWhere($orX);
        }

        $qb = $this->buildFindBy($qb, $where, $join, $orderBy);

        return $qb;
    }

    protected function buildFindBy(QueryBuilder $qb, array $where, array $join, array $orderBy)
    {
        $i = 1;
        foreach ($where as $k => $v) {
            $token = 'eqMatch'.$i;
            $qb->andWhere($qb->expr()->eq($k, ':'.$token))->setParameter($token, $v);
            $i++;
        }

        foreach ($join as $k => $v) {
            $qb->leftJoin($k, $v);
            $qb->addSelect($v);
        }

        foreach ($orderBy as $k => $v) {
            $qb->addOrderBy($k, $v);
        }

        return $qb;
    }
}
