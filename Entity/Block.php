<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class Block
{
    use \Msi\CmfBundle\Doctrine\Extension\Model\Translatable;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $slot;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @ORM\Column(type="array")
     */
    protected $settings;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published;

    protected $rendered;

    public function __construct()
    {
        $this->rendered = false;
        $this->published = false;
        $this->settings = array();
        $this->position = time();
        $this->pages = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->operators = new ArrayCollection();
    }

    public function getRendered()
    {
        return $this->rendered;
    }

    public function setRendered($rendered)
    {
        $this->rendered = $rendered;

        return $this;
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

    public function getSlot()
    {
        return $this->slot;
    }

    public function setSlot($slot)
    {
        $this->slot = $slot;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;

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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSetting($key)
    {
        return array_key_exists($key, $this->settings) ? $this->settings[$key] : null;
    }

    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
