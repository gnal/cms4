<?php

namespace Msi\CmfBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

use Msi\CmfBundle\Doctrine\Extension\BaseListener;

class SortableListener extends BaseListener
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($this->isEntitySupported($e, 'Msi\CmfBundle\Doctrine\Extension\Model\Sortable')) {
            $entity->setPosition(time());
        }
    }
}
