<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;



use Lci\BoilerBoxBundle\Entity\Configuration;


class SiteConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			 ->add('configuration', EntityType::class, array (
            'class'           => 'LciBoilerBoxBundle:Configuration',
            'label'           => false,
            'choice_label'    => 'parametre',
            ))
			->add('valeur',  TextType::class, array(
                    'label'         => false,
                    'attr'          => array (
                        'placeholder'   => 'Valeur du paramètre',
                    ),
                    'required'      => true,
                    'trim'          => true
			))

/*
			->add('configuration', EntityType::class, array (
            'class'           => 'LciBoilerBoxBundle:Configuration',
            'label'           => 'Configuration',
            'choice_label'    => 'parametre',
            'query_builder'   => function(EntityRepository $er){
                    return $er->createQueryBuilder('c')
                        ->leftJoin('c.siteConfigurations', 'sc')
                        ->where('sc.type LIKE :type')
                        ->setParameter('type', 'connexion')
                        ->orderBy('c.parametre', 'ASC');
                },
            ))

/*
			->add('configuration', CollectionType::class, array(
				'entry_type'    => ConfigurationType::class,
                'label'         => false,
                'allow_add'     => true,
                'allow_delete'  => true,
				'mapped'		=> false
			))
  */
          ->add('submit', SubmitType::class,  ['label' => 'Save', 'attr' => ['class' => 'cacher']]);
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
