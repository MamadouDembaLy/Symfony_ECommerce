<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/produits', name: 'products_')]

class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
        ]);
    }

    #[Route('/{slug}', name: 'details')] //l'acolade dit a symfony que slug est une variable 
    public function details(Products $product ,CategoriesRepository $categoriesRepository): Response
    {
             
        $categories = $categoriesRepository->findAll();
        

        return $this->render('products/details.html.twig',compact('product','categories')); 
    }
}
