<?php

namespace Msi\CmfBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class PageController extends ContainerAware
{
    public function showAction(Request $request)
    {
        $criteria = [
            'a.published' => true,
            'a.site' => $this->container->get('msi_cmf.provider')->getSite(),
        ];

        $criteria['t.slug'] = $request->attributes->get('slug');

        $qb = $this->container->get('msi_cmf.page_manager')->getFindByQueryBuilder(
            $criteria,
            ['a.translations' => 't', 'a.blocks' => 'b'],
            ['b.position' => 'ASC']
        );
        $qb->andWhere($qb->expr()->isNull('a.route'));
        $page = $qb->getQuery()->getOneOrNullResult();

        if (!$page) {
            throw new NotFoundHttpException();
        }

        return $this->container->get('templating')->renderResponse($page->getTemplate(), ['page' => $page]);
    }
}
