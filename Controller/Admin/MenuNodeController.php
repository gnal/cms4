<?php

namespace Msi\CmfBundle\Controller\Admin;

use Msi\CmfBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;

class MenuNodeController extends CoreController
{
    public function sortAction(Request $request)
    {
        $id = $request->query->get('id');
        $node = $this->admin->getObjectManager()->getFindByQueryBuilder(
            ['a.id' => $request->query->get('id')]
        )->getQuery()->getOneOrNullResult();

        foreach ($request->query->get('array1') as $k => $v) {
            if ($v == $id) {
                $start = $k;
            }
        }

        foreach ($request->query->get('array2') as $k => $v) {
            if ($v == $id) {
                $end = $k;
            }
        }

        $number = $start - $end;

        if ($number > 0) {
            $this->admin->getObjectManager()->moveUp($node, abs($number));
        } elseif ($number < 0) {
            $this->admin->getObjectManager()->moveDown($node, abs($number));
        }

        return $this->redirect($this->admin->genUrl('list'));
    }
}
