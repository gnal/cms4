<?php

namespace Msi\CmfBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

use Msi\CmfBundle\Doctrine\Extension\BaseListener;

class SoftDeletableListener extends BaseListener
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(EventArgs $e)
    {
        $em = $e->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $metadata = $e->getEntityManager()->getClassMetadata(get_class($entity));
            if ($this->getClassAnalyzer()->hasTrait($metadata->reflClass, 'Msi\CmfBundle\Doctrine\Extension\Model\SoftDeletable')) {
                $oldValue = $entity->getDeletedAt();
                $entity->setDeletedAt(new \DateTime());

                $em->persist($entity);

                $uow->propertyChanged($entity, 'deletedAt', $oldValue, $entity->getDeletedAt());
                $uow->scheduleExtraUpdate($entity, [
                    'deletedAt' => [$oldValue, $entity->getDeletedAt()]
                ]);
            }
        }
    }
}
