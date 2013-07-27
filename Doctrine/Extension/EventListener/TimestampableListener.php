<?php

namespace Msi\AdminBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

use Msi\AdminBundle\Doctrine\Extension\BaseListener;

class TimestampableListener extends BaseListener
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Timestampable')) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    public function preUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Timestampable')) {
            $entity->setUpdatedAt(new \DateTime());

            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }
}
