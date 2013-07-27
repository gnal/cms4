<?php

namespace Msi\AdminBundle\Pager;

class PagerFactory
{
    public function create($query, array $options = array())
    {
        return new Pager($query, $options);
    }
}
