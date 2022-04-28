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
            	'label'           => 'Parametre',
            	'choice_label'    => 'parametre'
            ))
			->add('valeur',  TextType::class, array(
                    'label'         => 'Valeur',
                    'attr'          => array (
                        'placeholder'   => 'Valeur du paramètre',
                    ),
                    'required'      => true,
                    'trim'          => true
			))
			->add('type', ChoiceType::class, array(
					'label' => 'Type du paramètre',
					'choices' => [
						'Connexion' => 'connexion',
						'Autre'		=> 'autre',
						'Infos'		=> 'infos'
					],
                    'required'      => true,
            ))
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
