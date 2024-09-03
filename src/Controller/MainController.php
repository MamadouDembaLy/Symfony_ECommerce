<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(CategoriesRepository $categoriesRepository): Response //categoriesRepository permet d'interroger la base de donnes
    {
        //$categoriesRepository = $categoriesRepository->find
        return $this->render('main/index.html.twig',[
            'categories' =>$categoriesRepository->findBy([],['CategoryOrder'=> 'asc']) //permet de transmettre a notre vue les categories recuperer en ordre croissant
        ]);
    }
}
