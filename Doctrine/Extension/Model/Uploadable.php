<?php

namespace Msi\CmfBundle\Doctrine\Extension\Model;

trait Uploadable
{
    public function getUploadDir($fieldName)
    {
        if (!in_array($fieldName, $this->getUploadFields())) {
            throw new \InvalidArgumentException('upload field name "'.$fieldName.'" doesn\'t exist for entity '.get_class($this));
        }

        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        $class = lcfirst($class);
        $class = preg_replace_callback('|([A-Z])|', function($matches) {
            return '-'.strtolower($matches[0]);
        }, $class);

        $suffix = method_exists($this, 'getUploadDirSuffix') ? '/'.$this->getUploadDirSuffix() : '';

        return strtolower($class.'-'.array_search($fieldName, $this->getUploadFields()).$suffix);
    }

    public function getPathname($fieldName, $prefix = '')
    {
        if (!in_array($fieldName, $this->getUploadFields())) {
            throw new \InvalidArgumentException('upload field name "'.$fieldName.'" doesn\'t exist for entity '.get_class($this));
        }

        $getter = 'get'.ucfirst($fieldName);

        return '/uploads/'.$this->getUploadDir($fieldName).'/'.$prefix.$this->$getter();
    }
}
