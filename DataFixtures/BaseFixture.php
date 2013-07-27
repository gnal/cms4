<?php

namespace Msi\AdminBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\Collections\ArrayCollection;

abstract class BaseFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function create(array $values = [], array $translations = [], $referenceName = null)
    {
        $entity = $this->manager->create();

        foreach ($values as $key => $value) {
            $setter = 'set'.ucfirst($key);
            $getter = 'get'.ucfirst($key);
            if ($entity->$getter() instanceof ArrayCollection) {
                foreach ($value as $v) {
                    $entity->$getter()->add($v);
                }
            } else {
                $entity->$setter($value);
            }
        }

        if (count($translations)) {
            $this->manager->createTranslations($entity, array_keys($translations));
        }

        foreach ($translations as $locale => $val) {
            if (!is_array($val)) {
                throw new \InvalidArgumentException('the translations argument must be array. given: '.var_export($val, true));
            }
            foreach ($val as $key => $value) {
                $setter = 'set'.ucfirst($key);
                $entity->getTranslation($locale)->$setter($value);
            }
        }

        if (null !== $referenceName) {
            $this->addReference($referenceName, $entity);
        }

        $this->manager->update($entity);
    }

    public function getRef($name)
    {
        return $this->manager->getEntityManager()->merge($this->getReference($name));
    }

    public function getOrder()
    {
        return 1;
    }
}
