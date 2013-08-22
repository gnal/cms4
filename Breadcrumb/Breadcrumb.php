<?php

namespace Msi\AdminBundle\Breadcrumb;

class Breadcrumb
{
    protected $crumbs;

    public function add($label, $url = null, $options = [])
    {
        $this->crumbs[] = new Crumb($label, $url, $options);

        return $this;
    }

    public function all()
    {
        return $this->crumbs;
    }

    public function remove($key)
    {
        if (isset($this->crumbs[$key]) || array_key_exists($key, $this->crumbs)) {
            $removed = $this->crumbs[$key];
            unset($this->crumbs[$key]);

            return $removed;
        }

        return null;
    }

    public function get($key)
    {
        if (isset($this->crumbs[$key])) {
            return $this->crumbs[$key];
        }

        return null;
    }
}
