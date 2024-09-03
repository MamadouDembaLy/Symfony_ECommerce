<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;




#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
     
   #[Route('/{slug}', name: 'list')] //l'acolade dit a symfony que slug est une variable 
    public function list(Categories $category,ProductsRepository $productsRepository,Request $request): Response
    {
        //On va chercher le numero de page dans l'url
        $page = $request->query->getInt('page',1);
     
        //on va chercher la liste des produits de la categorie
        $products = $productsRepository->findProductsPaginated($page, $category->getSlug(), 3);  
        

        return $this->render('categories/list.html.twig',compact('category','products'));
    }
}
