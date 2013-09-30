<?php

namespace Msi\AdminBundle\Controller;

use Msi\BaseBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function limitAction()
    {
        $limit = intval($this->getRequest()->request->get('limit'));

        if ($limit < 1) {
            $limit = 25;
        }

        $this->get('session')->set('limit', $limit);

        if ($_SERVER['HTTP_REFERER']) {
            $url = preg_replace('@\??&?page=\d+@', '', $_SERVER['HTTP_REFERER']);
        } else {
            $url = '/';
        }

        return $this->redirect($url);
    }

    public function workingLocaleAction()
    {
        $locale = $this->getRequest()->query->get('locale');

        $this->get('session')->set('working_locale', $locale);

        return $this->redirect($_SERVER['HTTP_REFERER'] ?: $this->generateUrl('msi_admin_dashboard'));
    }

    public function tinymceloginAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return new Response("You don't have access to this page.");
        }

        $url = $this->getRequest()->query->get('return_url');
        $configuration = ['configs' => ['my.key' => 'value']];

        $key = md5(implode('', $configuration['configs']).'someSecretKey');

        return $this->render('MsiAdminBundle:Default:tinymcelogin.html.twig', [
            'configuration' => $configuration,
            'url' => $url,
            'key' => $key
        ]);
    }
}
