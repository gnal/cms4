<?php

namespace Msi\CmfBundle\Doctrine;

use Doctrine\ORM\EntityManager;
use Msi\CmfBundle\Tools\ClassAnalyzer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\QueryBuilder;

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

    public function createTranslations($entity, array $locales)
    {
        $class = $this->class.'Translation';
        foreach ($locales as $locale) {
            if (!$entity->hasTranslation($locale)) {
                $translation = new $class();
                $translation->setLocale($locale)->setObject($entity);
                $entity->getTranslations()->add($translation);
            }
        }
    }

    public function toggle($entity, $request)
    {
        $field = $request->query->get('field');
        $locale = $request->query->get('locale');

        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        if ($locale) {
            $entity->getTranslation($locale)->$getter()
                ? $entity->getTranslation($locale)->$setter(false)
                : $entity->getTranslation($locale)->$setter(true);
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

    public function findOneOrCreate(array $locales, $id = null)
    {
        if ($id) {
            $object = $this->getOneBy(['a.id' => $id]);
        } else {
            $object = $this->create();
        }

        if ($this->classAnalyzer->hasTrait($this->getMetadata()->reflClass, 'Msi\CmfBundle\Doctrine\Extension\Model\Translatable')) {
            $this->createTranslations($object, $locales);
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
            throw new NotFoundHttpException();
        }

        return $row;
    }

    public function findAll(array $where = [], array $join = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $rows = $this->getMasterQueryBuilder($where, $join, $orderBy, $limit, $offset)->getQuery()->execute();

        return $rows;
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
