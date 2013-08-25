<?php

namespace Msi\AdminBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Msi\AdminBundle\Doctrine\Extension\BaseListener;

class TranslatableListener extends BaseListener
{
    protected $container;

    // cuz sometimes we need not to enter the request scope ie: in commands
    protected $skipPostLoad;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
        $this->skipPostLoad = false;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::postLoad,
            Events::loadClassMetadata,
        );
    }

    public function postLoad(EventArgs $e)
    {
        $entity = $e->getEntity();

        if ($this->skipPostLoad === false && $this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Translatable')) {
            $entity->setRequestLocale($this->container->get('request')->getLocale());
        }
    }

    public function setSkipPostLoad($skipPostLoad)
    {
        $this->skipPostLoad = $skipPostLoad;

        return $this;
    }

    public function loadClassMetadata(EventArgs $e)
    {
        $metadata = $e->getClassMetadata();

        if ($this->getClassAnalyzer()->hasTrait($metadata->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Translatable')) {
            if (!$metadata->isMappedSuperclass && !$metadata->hasAssociation('translations')) {
                $metadata->mapOneToMany([
                    'fieldName' => 'translations',
                    'targetEntity' => $metadata->reflClass->getName().'Translation',
                    'mappedBy' => 'object',
                    'orderBy' => ['locale' => 'ASC'],
                    'cascade' => ['persist'],
                ]);
            }
        }

        if ($this->getClassAnalyzer()->hasTrait($metadata->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Translation')) {
            if (!$metadata->isMappedSuperclass && !$metadata->hasAssociation('object')) {
                $metadata->mapManyToOne([
                    'fieldName' => 'object',
                    'targetEntity' => str_replace('Translation', '', $metadata->reflClass->getName()),
                    'inversedBy' => 'translations',
                    'joinColumns' => [
                        'object_id' => ['onDelete' => 'CASCADE'],
                    ],
                ]);
            }

            $name = $metadata->getTableName().'_unique_translation';
            if (!$this->hasUniqueTranslationConstraint($metadata, $name)) {
                $metadata->setPrimaryTable([
                    'uniqueConstraints' => [[
                        'name' => $name,
                        'columns' => ['object_id', 'locale' ]
                    ]],
                ]);
            }
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadata $classMetadata, $name)
    {
        if (isset($classMetadata->table['uniqueConstraints'])) {
            foreach ($classMetadata->table['uniqueConstraints'] as $value) {
                if (isset($value['name']) && $value['name'] === $name) {
                    return true;
                }
            }
        }

        return false;
    }
}
