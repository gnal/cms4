<?php

namespace Msi\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Msi\AdminBundle\Model\MenuTranslation as BaseMenuTranslation;

/**
 * @ORM\Entity
 */
class MenuTranslation extends BaseMenuTranslation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
