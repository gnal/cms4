<?php

namespace Msi\AdminBundle\Doctrine\Extension\Model;

trait Blameable
{
    protected $createdBy;

    protected $updatedBy;

    protected $deletedBy;

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    public function setDeletedBy($deletedBy)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }
}
