<?php

namespace Msi\AdminBundle\Doctrine\Extension\Model;

trait Translation
{
    /**
     * @ORM\Column(type="string")
     */
    protected $locale;

    protected $object;

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }
}
