<?php
//formulaire de l'email lors de la perte du mot de passe
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        //ce ligne de code nous permet d'ajouter des champs avec des attribut et classes sur le formulaire de notre vue reset_password_request
            ->add('email',EmailType::class,['attr'=>['class'=>'form-control','placeholder'=>'Exemple@gmail.com'],'label'=>"Saisir votre Email"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
