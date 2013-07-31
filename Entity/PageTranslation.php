<?php

namespace Msi\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Msi\AdminBundle\Model\PageTranslation as BasePageTranslation;

/**
 * @ORM\Entity
 */
class PageTranslation extends BasePageTranslation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
