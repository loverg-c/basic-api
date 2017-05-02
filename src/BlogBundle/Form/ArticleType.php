<?php

namespace BlogBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content')
            ->add(
                'author',
                EntityType::class,
                array(
                    // query choices from this entity
                    'class' => 'AppBundle\Entity\User',
                )
            )
            ->add(
                'category',
                EntityType::class,
                array(
                    // query choices from this entity
                    'class' => 'BlogBundle\Entity\Category',
                )
            )
            ->add('tags');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BlogBundle\Entity\Article',
                'csrf_protection' => false,
            )
        );
    }
}