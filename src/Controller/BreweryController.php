<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BreweryController extends AbstractController
{

    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('brewery/login.html.twig', [
            'controller_name' => 'BreweryController',
        ]);
    }

    #[Route('/breweries', name: 'app_breweries')]
    public function getBreweries(): Response
    {
        return $this->render('brewery/list.html.twig', [
            'controller_name' => 'BreweryController',
        ]);
    }
}
