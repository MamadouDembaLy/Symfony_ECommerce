<?php
//fichier qui gére les permissions pour le CRUD en utilisant egalement security yaml
namespace App\Security\Voter;

use App\Entity\Products;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductsVoter extends Voter
{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    //fonction qui verifie si les differentes paramétre sont bbons
    protected function supports(string $attribute, $product) : bool
    {
        if(!in_array($attribute,[self::EDIT,self::DELETE]))
        {
            return false;
        }
        if(!$product instanceof Products)
        {
            return false;
        }

        return true;

       // simplification du code en dessus(return in_array($attribute,[self::EDIT,self::DELETE]) && $product instanceof Products;)
    }

    protected function voteOnAttribute($attribute, $product, TokenInterface $token): bool
    {
       //on recupere l'utilisateur a partir du token
       $user = $token->getUser();

       //on verifie si l'utilisateur est connecté ou pas
       if(!$user instanceof UserInterface) return false;

       //on verifie si l'utilisateur est admin
       if($this->security->isGranted('ROLE_ADMIN')) return true ;

       //on vérifie les permissions
       switch($attribute){
        case self::EDIT :
        //on verifie si l'utilisateur peut éditer
        return $this->canEdit();

        break;

        case self::DELETE :
        //on vérifie si l'utilisateur peut supprimer
        return $this->canDelete();
        break;
       }
    }

    private function canEdit(){
     return $this->security->isGranted('ROLE_PRODUCT_ADMIN');   
    }
    private function canDelete(){
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');   
       }
}