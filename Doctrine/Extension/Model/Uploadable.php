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

        // attempt at generating a good name for the folder ;)
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        $class = lcfirst($class);
        $class = preg_replace_callback('|([A-Z])|', function($matches) {
            return '-'.strtolower($matches[0]);
        }, $class);

        $prefix = method_exists($this, 'getUploadDirPrefix') ? $this->getUploadDirPrefix().'/' : '';
        $suffix = method_exists($this, 'getUploadDirSuffix') ? '/'.$this->getUploadDirSuffix() : '';

        return strtolower($prefix.$class.'-'.$fieldName.$suffix);
    }

    public function getPathname($fieldName = null, $prefix = '', $default = null)
    {
        if ($fieldName && !in_array($fieldName, $this->getUploadFields())) {
            throw new \InvalidArgumentException('upload field name "'.$fieldName.'" doesn\'t exist for entity '.get_class($this));
        }

        $fieldName = $fieldName ?: $this->getUploadFields()[0];

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
