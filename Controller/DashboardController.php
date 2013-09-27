<?php

namespace Msi\AdminBundle\Controller;

use Msi\BaseBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('MsiAdminBundle:Dashboard:dashboard.html.twig');
    }
}
