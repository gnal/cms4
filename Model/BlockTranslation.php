<?php

namespace Msi\AdminBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BlockTranslation
{
    use \Msi\AdminBundle\Doctrine\Extension\Model\Translation;
    use \Msi\AdminBundle\Doctrine\Extension\Model\Publishable;

    /**
     * @ORM\Column(type="array")
     */
    protected $settings;

    public function __construct()
    {
        $this->settings = [];
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

    public function getId()
    {
        return $this->id;
    }
}
