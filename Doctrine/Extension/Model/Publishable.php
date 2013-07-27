<?php

namespace Msi\AdminBundle\Doctrine\Extension\Model;

trait Publishable
{
    /**
     * @ORM\Column(type="boolean")
     */
    protected $published = false;

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }
}
