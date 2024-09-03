<?php

//cest le controller qui gére les le panier 
namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    //route d'accueil du pagner
    #[Route('/', name: 'index')]
    //le ProductRepository permet de faire des requettes dans la table products
    public function index( SessionInterface $session,ProductsRepository $productsRepository)
    { 
        $panier = $session->get('panier',[]);

        //on initialise des variables
        $data=[];
        $total = 0;

        //on boucle sur le panier pour chercher pour chaque produit y trouvant son nom,son prix,sa quantite dans le panier
       //on l'id et la quantite dans la session
        foreach($panier as $id => $quantity)
        {
            //on récupère le produit a partir de son id
           $product = $productsRepository->find($id);

           //on met dans le tableau data le produit plus sa quantité
           $data[]=[
               'product' => $product,
               'quantity' => $quantity
           ];
           //on calcule le total des produit de la carte en fonction de leur quantité
           $total += $product->getPrice() * $quantity ;
        }
       

    return $this->render('cart/index.html.twig',compact('data','total'));

    }



    //Route qui permet l'ajout d'un produit en fonction de l'id du produit
    #[Route('/add/{id}', name: 'add')]
    public function add(Products $product, SessionInterface $session)
    { 
        //on recupère l'id du produit
        $id = $product->getId();

        //on récupère le panier existant s'il n'ya pas de panier on met un tableau vide
        $panier = $session->get('panier',[]);

        //on ajoute le produit dans le panier s'il n'y est pas encore
        //sinon on incrémente sa quantité

        if(empty($panier[$id]))
        {
            $panier[$id] = 1;
        }
        else{
            $panier[$id]++;
        }

        $session->set('panier',$panier);

        //on redirige vers la page du panier
        return $this->redirectToRoute('cart_index');
      
    }



    //route et fonction qui permet de diminuer la quantité d'un produit
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Products $product, SessionInterface $session)
    { 
        //on recupère l'id du produit
        $id = $product->getId();

        //on récupère le panier existant s'il n'ya pas de panier on met un tableau vide
        $panier = $session->get('panier',[]);

        //on retire le produit du panier s'il n'ya qu'1 exemplaire
        //sinon on decrémente sa quantité

        if(!empty($panier[$id])){
            //si la quantité dans le panier est supérieur à 1
            //on décrémente
            if($panier[$id]>1){
                $panier[$id]--;
            }else{
                unset($panier[$id]);
            }
            
        }
        $session->set('panier',$panier);

        //on redirige vers la page du panier
        return $this->redirectToRoute('cart_index');
      
    }


    //on supprime un produit en fonction de l'id
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Products $product, SessionInterface $session)
    { 
        //on recupère l'id du produit
        $id = $product->getId();

        //on récupère le panier existant s'il n'ya pas de panier on met un tableau vide
        $panier = $session->get('panier',[]);

        //on verifie si le panier n'est pas et on supprime le produit en fonction de l'id
     if(!empty($panier[$id]))
        {
           
         unset($panier[$id]);
        }
            
        $session->set('panier',$panier);

        //on redirige vers la page du panier
        return $this->redirectToRoute('cart_index');
    } 

    //fonction et route qui vide le panier
    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session)
    { 
        $session->remove('panier');

        return $this->redirectToRoute('cart_index');
    }
    
}