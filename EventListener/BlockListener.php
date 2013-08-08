<?php

namespace Msi\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockListener implements EventSubscriber
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(EventArgs $e)
    {
        $metadata = $e->getClassMetadata();

        if ($metadata->name !== $this->container->getParameter('msi_admin.block.class')) {
            return;
        }

        if (!$metadata->hasAssociation('pages')) {
            $metadata->mapManyToMany([
                'fieldName'    => 'pages',
                'targetEntity' => $this->container->getParameter('msi_admin.page.class'),
                'inversedBy' => 'blocks',
                'cascade' => ['persist'],
            ]);
        }

        if (!$metadata->hasAssociation('operators')) {
            $metadata->mapManyToMany([
                'fieldName'    => 'operators',
                'targetEntity' => $this->container->getParameter('fos_user.model.group.class'),
            ]);
        }
    }
}
