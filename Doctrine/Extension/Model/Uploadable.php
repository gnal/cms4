<?php

namespace Msi\AdminBundle\Doctrine\Extension\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        return strtolower($class.'-'.$fieldName.$suffix);
    }

    public function getPathname($fieldName = 'filename', $prefix = '', $default = null)
    {
        if (!in_array($fieldName, $this->getUploadFields())) {
            throw new \InvalidArgumentException('upload field name "'.$fieldName.'" doesn\'t exist for entity '.get_class($this));
        }

        $getter = 'get'.ucfirst($fieldName);

        return $this->$getter() ? '/uploads/'.$this->getUploadDir($fieldName).'/'.$prefix.$this->$getter() : $default;
    }

    public function generateFileName(UploadedFile $file)
    {
        $arr = [];
        foreach ($this->getUploadFields() as $key => $value) {
            $arr[$value] = uniqid(time());
        }

        return $arr;
    }

    public function getUploadFields()
    {
        return ['filename'];
    }
}
