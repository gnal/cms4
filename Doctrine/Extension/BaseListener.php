<?php

namespace Msi\AdminBundle\Doctrine\Extension;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventArgs;
use Msi\AdminBundle\Tools\ClassAnalyzer;

abstract class BaseListener implements EventSubscriber
{
    public function __construct()
    {
        $this->classAnalyzer = new ClassAnalyzer;
    }

    public function getClassAnalyzer()
    {
        return $this->classAnalyzer;
    }

    public function isEntitySupported(EventArgs $e, $traitName)
    {
        $metadata = $e->getEntityManager()->getClassMetadata(get_class($e->getEntity()));

        return $this->getClassAnalyzer()->hasTrait($metadata->reflClass, $traitName);
    }

    abstract public function getSubscribedEvents();
}
