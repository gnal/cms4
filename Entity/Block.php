<?php

namespace Msi\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Msi\AdminBundle\Model\Block as BaseBlock;

/**
 * @ORM\Entity
 */
class Block extends BaseBlock
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Page", inversedBy="blocks", cascade={"persist"})
     */
    protected $pages;

    /**
     * @ORM\ManyToMany(targetEntity="Msi\UserBundle\Entity\Group")
     */
    protected $operators;
}
