<?php

namespace Msi\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class PageController extends ContainerAware
{
    public function showAction(Request $request)
    {
        $criteria = [
            'a.published' => true,
            'a.site' => $this->container->get('msi_admin.provider')->getSite(),
            't.locale' => $request->getLocale(),
        ];

        $criteria['t.slug'] = $request->attributes->get('slug');

        $qb = $this->container->get('msi_admin.page_manager')->getFindByQueryBuilder(
            $criteria,
            ['a.translations' => 't', 'a.blocks' => 'b'],
            ['b.position' => 'ASC']
        );

        $qb->andWhere($qb->expr()->isNull('a.route'));

        $parameters['page'] = $qb->getQuery()->getOneOrNullResult();

        if (!$parameters['page']) {
            throw new NotFoundHttpException();
        }

        return $this->container->get('templating')->renderResponse($parameters['page']->getTemplate(), $parameters);
    }
}
