<?php

namespace Msi\AdminBundle\Doctrine\Extension\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

use Msi\AdminBundle\Doctrine\Extension\BaseListener;

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
        if ($this->isEntitySupported($e, 'Msi\AdminBundle\Doctrine\Extension\Model\Sortable')) {
            $entity->setPosition(time());
        }
    }
}
