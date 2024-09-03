<?php
//security yaml
namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/products/index.html.twig',compact('produits'));
    }

    #[Route('/ajout', name: 'add')]
    //EntitymanagerInterface gere le stckage en base de données
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        //verifie si l'utilisateur a l'acces pour l'ajout des commandes
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //on cree un nouveau produits
        $product = new Products();

        //on crée le formulaire avec createForm  en specifiant le type
        $ProductForm = $this->createForm(ProductsFormType::class, $product);

        //on traite la requete du formulaire
        $ProductForm->handleRequest($request);

        //on verifie si le formulaire est soumis et valide
        if ($ProductForm->isSubmitted() && $ProductForm->isValid()) {

            //on recupere les images
            $images = $ProductForm->get('images')->getData();

            foreach ($images as $image) {
                //on defint le dossier de destination

                $folder = 'products';

                //on appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }


            //ON GENERE LE SLUG
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            //on arrondi le prix en multipliant par 100
            //$prix = $product->getPrice() * 100;
            //$product->setPrice($prix);

            //insertion dans la base de données
            $em->persist($product);
            $em->flush();

            $this->addFlash('dark', 'Produits ajouté avec succès');

            //on rediriges après l'execution du code

            return $this->redirectToRoute('admin_products_index');
        }


        return $this->render('admin/products/add.html.twig', [
            'ProductForm' => $ProductForm->createView()
        ]);
        //return $this->renderForm('admin/products/add.html.twig',compact('productForm'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        //verifie si l'utilisateur peut editer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        //$prix = $product->getPrice() / 100;
        //$product->setPrice($prix);


        //on crée le formulaire avec createForm  en specifiant le type
        $ProductForm = $this->createForm(ProductsFormType::class, $product);

        //on traite la requete du formulaire
        $ProductForm->handleRequest($request);

        //on verifie si le formulaire est soumis et valide
        if ($ProductForm->isSubmitted() && $ProductForm->isValid()) {

            //on recupere les images
            $images = $ProductForm->get('images')->getData();

            foreach ($images as $image) {
                //on defint le dossier de destination

                $folder = 'products';

                //on appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            //ON GENERE LE SLUG
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            //on arrondi le prix en multipliant par 100
            //$prix = $product->getPrice() * 100;
           // $product->setPrice($prix);

            //insertion dans la base de données
            $em->persist($product);
            $em->flush();

            $this->addFlash('dark', 'Modifier Avec succès');

            //on rediriges après l'execution du code

            return $this->redirectToRoute('admin_products_index');
        }


        return $this->render('admin/products/edit.html.twig', [
            'ProductForm' => $ProductForm->createView(), 'product' => $product
        ]);
        //return $this->renderForm('admin/products/add.html.twig',compact('productForm'));

        return $this->render('admin/products/index.html.twig');
    }
//chemin pour supprimer une image
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        //verifie si l'utilisateur peut editer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        return $this->render('admin/products/index.html.twig');
    }
//chemin pour supprimer une image
    #[Route('/suppression/image/{id}', name: 'delete_image',methods:['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        //on recupere l'id dans le controller 
        $data = json_decode($request->getContent(),true);

        if($this->isCsrfTokenValid('delete'. $image->getId(), $data['_token']))
        {
            //le token csrf est valide
            //on recupere le nom de l'image

            $nom = $image->getName();

            if($pictureService->delete($nom, 'products' , 300,300)){
                //on supprime l'image dans la base de données
                $em->remove($image);
                $em->flush();
                
                return new JsonResponse(['success'=>true],200);

            }
            //la suppression a echoué
            return new JsonResponse(['error'=>'Erreur de suppression'],400);
        }
        

        return new JsonResponse(['error'=>'token invalide'],400);
    }
}
