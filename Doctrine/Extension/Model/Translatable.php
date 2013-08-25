<?php

namespace Msi\AdminBundle\Doctrine\Extension\Model;

use Doctrine\Common\Collections\ArrayCollection;

trait Translatable
{
    protected $requestLocale;

    protected $translations;

    public function getTranslation($locale = null)
    {
        // if ($this->getTranslations()->count() === 0) {
        //     throw new \Exception('A translatable entity must have at least one translation. Translatable entity '.get_class($this).' has no translation.');
        // }

        if ($locale) {
            foreach ($this->getTranslations() as $translation) {
                if ($locale === $translation->getLocale()) {
                    return $translation;
                }
            }
        }

        foreach ($this->getTranslations() as $translation) {
            if ($this->requestLocale === $translation->getLocale()) {
                return $translation;
            }
        }

        if ($this->getTranslations()->count()) {
            return $this->getTranslations()->first();
        }

        return $this->createTranslation($locale ?: $this->requestLocale);
    }

    public function createTranslation($locale)
    {
        $class = get_class($this).'Translation';
        $translation = new $class();
        $translation->setLocale($locale)->setObject($this);
        $this->getTranslations()->add($translation);

        return $translation;
    }

    public function hasTranslation($locale)
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    public function getTranslations()
    {
        if (!$this->translations) {
            $this->translations = new ArrayCollection;
        }

        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    public function getRequestLocale()
    {
        return $this->requestLocale;
    }

    public function setRequestLocale($requestLocale)
    {
        $this->requestLocale = $requestLocale;

        return $this;
    }
}
