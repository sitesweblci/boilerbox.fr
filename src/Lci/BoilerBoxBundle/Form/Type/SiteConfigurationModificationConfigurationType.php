<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;

use Lci\BoilerBoxBundle\Entity\Configuration;


class SiteConfigurationModificationConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('configuration', EntityType::class, array (
                'class'           => 'LciBoilerBoxBundle:Configuration',
                'label'           => 'Paramètre',
                'choice_label'    => 'parametre',
                'placeholder'       => 'Choix du paramètre à modifier',
				'required'          => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.parametre', 'ASC');
                },
            ))
            ->add('submit', SubmitType::class,  array(
                'label' => 'Modifier',
                'attr'  => ['class' => 'btn btn--main']
            ));
    }

	/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\SiteConfiguration'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lci_boilerboxbundle_siteconfiguration';
    }


}
