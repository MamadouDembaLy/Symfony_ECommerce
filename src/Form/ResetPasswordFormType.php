<?php
//formulaire permettant de saisir les nouveaux mots de passe lors de la perte de ces derniers
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        //ce ligne de code nous permet d'ajouter des champs avec des attribut et classes sur le formulaire de notre vue reset_password_request
            ->add('password',PasswordType::class,['attr'=>['class'=>'form-control'],'label'=>"Entrez Votre Nouveau Mot de passe"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
