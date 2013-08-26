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
            'translations.published' => true,
            'a.site' => $this->container->get('msi_admin.provider')->getSite(),
            'translations.locale' => $request->getLocale(),
        ];

        $criteria['translations.slug'] = $request->attributes->get('slug');

        $qb = $this->container->get('msi_admin.page_manager')->getFindByQueryBuilder(
            $criteria,
            [
                'a.translations' => 'translations',
                'a.blocks' => 'blocks',
                'blocks.translations' => 'blocks_translations',
            ],
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
