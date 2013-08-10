Concrete entities for Doctrine ORM
==================================

The ORM implementation does not provide concrete entities.

Menu class
-------------

::

    <?php

    namespace Acme\AdminBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Gedmo\Mapping\Annotation as Gedmo;
    use Msi\AdminBundle\Entity\Menu as BaseMenu;

    /**
     * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
     */
    class Menu extends BaseMenu
    {
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @Gedmo\TreeParent
         * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
         */
        protected $parent;

        /**
         * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent", cascade={"remove"})
         * @ORM\OrderBy({"lft" = "ASC"})
         */
        protected $children;

        /**
         * @ORM\OneToMany(targetEntity="MenuTranslation", mappedBy="object", cascade={"persist", "remove"})
         */
        protected $translations;

        /**
         * @ORM\ManyToOne(targetEntity="Page")
         */
        protected $page;

        /**
         * @ORM\ManyToMany(targetEntity="Msi\UserBundle\Entity\Group")
         */
        protected $operators;
    }

MenuTranslation class
-------------

::

    <?php

    namespace Acme\AdminBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Msi\AdminBundle\Entity\MenuTranslation as BaseMenuTranslation;

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

        /**
         * @ORM\ManyToOne(targetEntity="Menu", inversedBy="translations")
         */
        protected $object;
    }

Page class
-------------

::

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
    }

PageTranslation class
-------------

::

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

Block class
-------------

::

    <?php

    namespace Acme\AdminBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Msi\AdminBundle\Entity\Block as BaseBlock;

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
         * @ORM\OneToMany(targetEntity="BlockTranslation", mappedBy="object", cascade={"persist", "remove"})
         */
        protected $translations;

        /**
         * @ORM\ManyToMany(targetEntity="Msi\UserBundle\Entity\Group")
         */
        protected $operators;
    }

BlockTranslation class
-------------

::

    <?php

    namespace Acme\AdminBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Msi\AdminBundle\Entity\BlockTranslation as BaseBlockTranslation;

    /**
     * @ORM\Entity
     */
    class BlockTranslation extends BaseBlockTranslation
    {
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\ManyToOne(targetEntity="Block", inversedBy="translations")
         */
        protected $object;
    }

Site class
-------------

::

    <?php

    namespace Acme\AdminBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Msi\AdminBundle\Entity\Site as BaseSite;

    /**
     * @ORM\Entity
     */
    class Site extends BaseSite
    {
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\OneToMany(targetEntity="SiteTranslation", mappedBy="object", cascade={"persist", "remove"})
         */
        protected $translations;
    }

SiteTranslation class
-------------

::

    <?php

    namespace Acme\AdminBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Msi\AdminBundle\Entity\SiteTranslation as BaseSiteTranslation;

    /**
     * @ORM\Entity
     */
    class SiteTranslation extends BaseSiteTranslation
    {
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\ManyToOne(targetEntity="Site", inversedBy="translations")
         */
        protected $object;
    }

Configure your application::

    msi_admin:
        site_class: Acme\AdminBundle\Entity\Site
        menu_class: Acme\AdminBundle\Entity\Menu
        page_class: Acme\AdminBundle\Entity\Page
        block_class: Acme\AdminBundle\Entity\Block
