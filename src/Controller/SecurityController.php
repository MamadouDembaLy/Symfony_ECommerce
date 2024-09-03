<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the 
        logout key on your firewall.');
    }

    //creation de route et de fonction pour l'oublie du mot de l'utilisateur
    #[Route(path: '/oubli-pass', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response {
        //creation du formulaire avec createForm avec le form de resetpasswordFormType dans le dossier form
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        //handlerequest gere une requete
        $form->handleRequest($request);

        //on verifie si le formulaire est envoyer  et valide avec les fonctions isSubmitted et isValid
        if ($form->isSubmitted() && $form->isValid()) {
            //on va chercher l'utilisateur a partir de son email.On va utiliser le userRepository pour faire une requette 
            //findOneBy est propriete 
            //getData permet daller chercher les informatuon dans le formulaore
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            //on verifie si on a un utilisateur dans la table user de notre base de donnés avec cet email
            if ($user) {
                //on genere un token de reinitialisation avec symfony: tokenGeneratorInterface a implemente dans la fonction
                $token = $tokenGenerator->generateToken();

                $user->setResetToken($token);
                //l'entityManager pour persister la requette en base de données

                $entityManager->persist($user);

                $entityManager->flush();

                //génére le liens de reinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                //on crée les données du mail
                //$context = ['user'=>$user,'url'=>$url];
                $context = compact('url', 'user');

                //envoie du mail
                $mail->send(
                    'ecommerce@gmail.com', //l'email de l'envoyeur
                    $user->getEmail(), //email destinataire
                    'reinitialisation du mot de passe', //titre ou object
                    'password_reset', //template a utilise
                    $context //contexte
                );

                $this->addFlash('succes', 'Email envoyé avec succés');
                return $this->redirectToRoute('app_login');
            }

            //s'il n'y est pas on affiche l'alerte et on le redirige vers le formulaire de connexion
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', ['requestPassForm' => $form->createView()]);
        //tu me cree la vue html de mon formulaire et
        //tul a passe a ma vue reset_password_request sous le nom de requestPassForm
    }

    #[Route(path: '/oubli-pass/{token}', name: 'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        //on verifie si on a ce token dans la base de données
        $user = $userRepository->findOneByResetToken($token);

        //s'il y'a un utilisateur avec le token 
        if ($user) 
        {

            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                //On efface le token
                $user->setResetToken('');

                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $form->get('password')->getData())

                );
                $entityManager->persist($user);

                $entityManager->flush();

                $this->addFlash('success', 'mot de passe changé avec succés');

                return $this->redirectToRoute(('app_login'));
            }

            return $this->render('security/reset_password.html.twig', ['passForm' => $form->createView()]);
        }

        $this->addFlash('danger', 'Jeton ivalide');

        return $this->redirectToRoute('app_login');
    }
}
