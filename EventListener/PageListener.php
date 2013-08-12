<?php

namespace Msi\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PageListener implements EventSubscriber
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

        if ($metadata->name !== $this->container->getParameter('msi_admin.page.class')) {
            return;
        }

        if (!$metadata->hasAssociation('site')) {
            $metadata->mapManyToOne([
                'fieldName'    => 'site',
                'targetEntity' => $this->container->getParameter('msi_admin.site.class'),
                'joinColumns' => [
                    [
                        'onDelete' => 'CASCADE',
                    ],
                ],
            ]);
        }

        if (!$metadata->hasAssociation('blocks')) {
            $metadata->mapManyToMany([
                'fieldName'    => 'blocks',
                'targetEntity' => $this->container->getParameter('msi_admin.block.class'),
                'mappedBy' => 'pages',
            ]);
        }
    }
}
