<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Products;
use App\Repository\CategoriesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProductsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', options: [
                'label' => 'Nom',
                
                ])
            ->add('description')
            ->add('price',MoneyType::class, options: [
                'label' => 'Prix',
                'divisor'=>100,
                'constraints' => [
                    new Positive(
                        message:'le prix ne peut etre negatif'
                        )
                    ] 
                ])
            ->add('stock', options: [
                'label' => 'Unités en stocke'
                ])
            //pour eviter ce message d'erreur:Object of class App\Entity\Categories could not be converted to string
            //il faut le code apres categories
            ->add('categories', EntityType::class, [
                'class' => Categories::class, 'choice_label' => 'name', 'label' => 'catégorie', 'group_by' => 'parent.name', 'query_builder' => function (CategoriesRepository $categoriesRepository) {
                    return $categoriesRepository->createQueryBuilder('c')
                        ->where('c.parent IS NOT NULL')
                        ->orderBy('c.name', 'ASC');
                }
            ])
            ->add('images',FileType::class,[
                'label'=>false, 
                'multiple' =>true,
                'mapped'=>false,
                'required'=>false ,
                'constraints' => [
                    //le new all permet d'ajouter plusieur images multiple
                    new All(
                        new Image([
                            'maxWidth'=> 1280,
                            'maxWidthMessage' => 'L\'image doit faire {{max_width}} pixels de large au maximum '
                        ])
 
                    )
                ]
            ])
            ;
        //le choice label permet d'avoir une liste deroulante des differents categories
        //le group_by permet de regrouper les parents de la categories
        //le query builder permet de faire requette pour aller chercher les informations qu'on souhaite 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
