<?php

namespace Msi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('MsiAdminBundle:Dashboard:dashboard.html.twig');
    }
}
