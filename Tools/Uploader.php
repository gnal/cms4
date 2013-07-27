<?php

namespace Msi\CmfBundle\Tools;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class Uploader
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    public function getUploadDir($dirname)
    {
        return $this->kernel->getRootDir().'/../web/uploads/'.$dirname;
    }

    public function removeUpload($fieldName, $entity)
    {
        $finder = new Finder();
        $dirname = $entity->getUploadDir($fieldName);

        // avoir error if dir doesnt exist
        if (!is_dir($this->getUploadDir($dirname))) {
            return;
        }

        $getter = 'get'.ucfirst($fieldName);
        $finder->files()->name('/'.$entity->$getter().'$/')->in($this->getUploadDir($dirname));

        foreach ($finder as $file) {
            unlink($file);
        }

        // if we upload in sub directories like with parent ID name, we delete these directories when they re empty
        if (preg_match('@/@', $dirname)) {
            @rmdir($this->getUploadDir($dirname));
        }
    }

    // remove old upload and create database entry
    public function preUpload($entity)
    {
        foreach ($entity->getUploadFields() as $k => $fieldName) {
            $getter = 'get'.ucfirst($k);
            $file = $entity->$getter();

            if ($file === null) continue;

            // if entity isnt new then it means we are uploading a new file therefore we have to remove old upload
            if ($entity->getId()) {
                $this->removeUpload($fieldName, $entity);
            }

            $setter = 'set'.ucfirst($fieldName);
            $entity->$setter(uniqid(time()).'.'.$file->guessExtension());
        }
    }

    // move file to upload folder and process it (resize, etc)
    public function postUpload($entity)
    {
        foreach ($entity->getUploadFields() as $k => $fieldName) {
            $getter = 'get'.ucfirst($k);
            $file = $entity->$getter();

            if ($file === null) continue;

            // move file
            $getter = 'get'.ucfirst($fieldName);
            $file = $file->move($this->getUploadDir($entity->getUploadDir($fieldName)), $entity->$getter());

            // process file
            $method = 'process'.ucfirst($k);
            if (method_exists($entity, $method)) {
                $entity->$method($file);
            }

            unset($file);
        }
    }
}
