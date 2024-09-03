<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commandes', name: 'app_orders_')]
class OrdersController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $em): Response
    {
        //on verifie si l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        $panier = $session->get('panier', []);

        if ($panier === []) {

            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('app_main');
        }
        //le panier n'est pas vide on crée la commande
        $order = new Orders();

        //on remplit la commande
        $order->setUsers($this->getUser());
        $order->setReference(uniqid());

        //on parcour le panier pour créer les détails de commandes
        foreach ($panier as $item => $quantity) {

            $orderDetails = new OrdersDetails();

            //on va chercher le produit
            $product = $productsRepository->find($item);

            $price = $product->getPrice();

            //on cree le detail de commande
            $orderDetails->setProducts($product);
            $orderDetails->setPrice($price);
            $orderDetails->setQuantity($quantity);

            $order->addOrdersDetail($orderDetails); //
        }
        //on persit et on flush
        $em->persist($order);
        $em->flush();

        //on vide le panier pour pas recrée des doublons de commandesçà
        $session->remove('panier');

        $this->addFlash('message', 'commande crée avec succès');
        return $this->redirectToRoute('app_main');
    }
}
