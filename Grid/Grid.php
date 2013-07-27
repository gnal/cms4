<?php

namespace Msi\AdminBundle\Grid;

use Doctrine\Common\Collections\Collection;

class Grid
{
    protected $admin;
    protected $rows;
    protected $columns;
    protected $sortable = false;

    public function getAdmin()
    {
        return $this->admin;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    public function getSortable()
    {
        return $this->sortable;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function setRows(Collection $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }
}
