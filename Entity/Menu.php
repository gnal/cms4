<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 * @Gedmo\Tree(type="nested")
 */
abstract class Menu
{
    use \Msi\CmfBundle\Doctrine\Extension\Model\Timestampable;
    use \Msi\CmfBundle\Doctrine\Extension\Model\Translatable;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer")
     */
    protected $menu;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $targetBlank;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->targetBlank = false;
        $this->published = false;
        $this->operators = new ArrayCollection();
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function getOperators()
    {
        return $this->operators;
    }

    public function setOperators($operators)
    {
        $this->operators = $operators;

        return $this;
    }

    public function getTargetBlank()
    {
        return $this->targetBlank;
    }

    public function setTargetBlank($targetBlank)
    {
        $this->targetBlank = $targetBlank;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function setOption($k ,$v)
    {
        $this->options[$k] = $v;
    }

    public function addChild($child)
    {
        $this->children[] = $child;

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getToTree()
    {
        $prefix = '';
        for ($i=0; $i < $this->lvl; $i++) {
            $prefix .= '- ';
        }

        if ($this->lvl === 0) {
            $name = $prefix.'Root';
        } else {
            $name = $prefix.$this->getTranslation()->getName();
        }

        return $name;
    }

    public function __toString()
    {
        return (string) $this->getTranslation()->getName();
    }
}
