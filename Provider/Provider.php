<?php

namespace Msi\CmfBundle\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Provider
{
    protected $siteManager;
    protected $site;
    protected $request;

    public function __construct(Request $request, $siteManager)
    {
        $this->siteManager = $siteManager;
        $this->request = $request;
        $site = null;
    }

    public function getSite()
    {
        if (!$this->site) {
            $site = $this->siteManager->getFindByQueryBuilder(
                ['a.host' => $this->request->getHost()],
                ['a.translations' => 't']
            )->getQuery()->getOneOrNullResult();

            if (!$site) {
                $site = $this->siteManager->getFindByQueryBuilder(
                    ['a.isDefault' => true],
                    ['a.translations' => 't']
                )->getQuery()->execute();
                if (!isset($site[0])) {
                    throw new NotFoundHttpException('No site was found');
                }
                $site = $site[0];
            }

            $this->site = $site;
        }

        return $this->site;
    }
}
