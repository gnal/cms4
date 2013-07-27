<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uniq_slug_locale", columns={"slug", "locale"}), @ORM\UniqueConstraint(name="uniq_object_id_locale", columns={"object_id", "locale"})})
 * @ORM\MappedSuperclass
 */
abstract class PageTranslation
{
    use \Msi\CmfBundle\Doctrine\Extension\Model\Translation;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $body;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metaKeywords;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metaDescription;

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }
}
