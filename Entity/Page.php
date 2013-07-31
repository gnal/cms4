<?php

namespace Msi\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Msi\AdminBundle\Model\Page as BasePage;

/**
 * @ORM\Entity
 */
class Page extends BasePage
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Block", mappedBy="pages")
     */
    protected $blocks;

    /**
     * @ORM\ManyToOne(targetEntity="Site")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $site;
}
