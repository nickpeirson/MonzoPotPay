<?php

namespace App\Controller;

use App\Monzo\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @var Authenticator */
    private $monzoAuth;

    public function __construct(Authenticator $monzoAuth)
    {
        $this->monzoAuth = $monzoAuth;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction(): Response
    {
        if (!$this->monzoAuth->loadCredentials($this->getUser())) {
            return $this->connectMonzo();
        }
        return $this->redirectToRoute('monzo_accounts');
    }

    private function connectMonzo(): Response
    {
        return $this->render('index.html.twig',
            [
                'monzo_auth_url' => $this->monzoAuth->getAuthURL()
            ]
        );
    }
}