<?php

namespace Msi\AdminBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Msi\AdminBundle\Doctrine\Extension\BaseListener;

class BlameableListener extends BaseListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
        );
    }

    public function prePersist(EventArgs $e)
    {
        if ($this->getUser() && $this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Blameable')) {
            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $entity = $e->getEntity();

            $oldValue = $entity->getCreatedBy();
            $entity->setCreatedBy($this->getUser());
            $uow->propertyChanged($entity, 'createdBy', $oldValue, $entity->getCreatedBy());
            $uow->scheduleExtraUpdate($entity, [
                'createdBy' => [$oldValue, $entity->getCreatedBy()],
            ]);
        }
    }

    public function preUpdate(EventArgs $e)
    {
        if ($this->getUser() && $this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Blameable')) {
            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $entity = $e->getEntity();

            $oldValue = $entity->getUpdatedBy();
            $entity->setUpdatedBy($this->getUser());
            $uow->propertyChanged($entity, 'updatedBy', $oldValue, $entity->getUpdatedBy());
            $uow->scheduleExtraUpdate($entity, [
                'updatedBy' => [$oldValue, $entity->getUpdatedBy()],
            ]);
        }
    }

    public function preRemove(EventArgs $e)
    {
        if ($this->getUser() && $this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Blameable')) {
            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $entity = $e->getEntity();

            $oldValue = $entity->getDeletedBy();
            $entity->setDeletedBy($this->getUser());
            $uow->propertyChanged($entity, 'deletedBy', $oldValue, $entity->getDeletedBy());
            $uow->scheduleExtraUpdate($entity, [
                'deletedBy' => [$oldValue, $entity->getDeletedBy()],
            ]);
        }
    }

    public function loadClassMetadata(EventArgs $e)
    {
        $userClass = $this->container->get('msi_user.user_manager')->getClass();
        $metadata = $e->getClassMetadata();
        if ($this->getClassAnalyzer()->hasTrait($metadata->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Blameable')) {
            if (!$metadata->hasAssociation('createdBy')) {
                $metadata->mapManyToOne([
                    'fieldName'    => 'createdBy',
                    'targetEntity' => $userClass,
                ]);
            }
            if (!$metadata->hasAssociation('updatedBy')) {
                $metadata->mapManyToOne([
                    'fieldName'    => 'updatedBy',
                    'targetEntity' => $userClass,
                ]);
            }
            if (!$metadata->hasAssociation('deletedBy')) {
                $metadata->mapManyToOne([
                    'fieldName'    => 'deletedBy',
                    'targetEntity' => $userClass,
                ]);
            }
        }
    }

    private function getUser()
    {
        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
