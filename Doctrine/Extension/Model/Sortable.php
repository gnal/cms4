<?php

namespace Msi\AdminBundle\Doctrine\Extension\Model;

trait Sortable
{
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
