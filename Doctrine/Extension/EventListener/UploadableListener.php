<?php

namespace Msi\AdminBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

use Msi\AdminBundle\Doctrine\Extension\BaseListener;

class UploadableListener extends BaseListener
{
    private $uploader;

    public function __construct($uploader)
    {
        parent::__construct();
        $this->uploader = $uploader;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        );
    }

    public function prePersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Uploadable')) {
            $this->uploader->preUpload($entity);
        }
    }

    public function preUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Uploadable')) {
            $this->uploader->preUpload($entity);
            $em   = $e->getEntityManager();
            $uow  = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }

    public function postPersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Uploadable')) {
            $this->uploader->postUpload($entity);
        }
    }

    public function postUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Uploadable')) {
            $this->uploader->postUpload($entity);
        }
    }

    public function postRemove(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Uploadable')) {
            foreach ($entity->getUploadFields() as $fieldName) {
                $this->uploader->removeUpload($fieldName, $entity);
            }
        }
    }
}
