<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UsersAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SendMailService $mail,
        JWTService $jwt
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);

            $entityManager->flush();
            // do anything else you need here, like send an email

            //on genere le jwt de l'utilisatteur
            //on cree le header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256',
            ];

            //on cree le payload
            $payload = [
                'user_id' => $user->getId()
            ];

            //on genere le token a la suite de ce qui est en haut
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret')); //le app.jwtsecret est configurer dans le service.yaml

            //on envoie un mail
            $mail->send(
                'Ecommerce@gmail.net',
                $user->getEmail(),
                'Activation de votre compte sur le site Ecommerce',
                'register',
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        //on verifie si le token est valide, n'a pas expiré et n'a pas été modifé
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {

            //on recupère le payload
            $payload = $jwt->getPayload($token);

            //on recupere le user du token
            $user = $userRepository->find($payload['user_id']);

            //on verifie que l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('succes', 'Utlisateur activé ');
                return $this->redirectToRoute('profile_index');
            }
        }
        //si un probleme se pose on affiche un message flash sur le navigateur
        $this->addFlash('danger', 'le token est invalide ou a expiré');
        //il retourne l'utilisateur a la page de connexion
        return $this->redirectToRoute('app_login');
    }

    //fonction qui permet de renvoyer le liens d'activation
    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UserRepository $userRepository): Response
    {
        //on recupere le user connecter
        $user = $this->getUser();

        //si l'utilisateur n'est pas connecter on le renvoie à la page login
        if (!$user) {
            $this->addFlash('danger', 'Vous devez etre connecté pour acceder à cette page');

            return $this->redirectToRoute('app_login');
        }

        if ($user->getIsVerified()) {

            $this->addFlash('warning', 'Cet utilisateur est deja activé');
            return $this->redirectToRoute('profile_index');
        }
        //on genere le jwt de l'utilisatteur
        //on cree le header
        $header = [
            'typ' => 'JWT',
            'alg' => 'H256',
        ];

        //on cree le payload
        $payload = [
            'user_id' => $user->getId()
        ];

        //on genere le token a la suite de ce qui est en haut
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret')); //le app.jwtsecret est configurer dans le service.yaml
        //on envoie un mail
        $mail->send(
            'Ecommerce@gmail.net',
            $user->getEmail(),
            'Activation de votre compte sur le site Ecommerce',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'Email de verification envoyé');
        //il retourne l'utilisateur a la page de connexion
        return $this->redirectToRoute('profile_index');
    }
}
