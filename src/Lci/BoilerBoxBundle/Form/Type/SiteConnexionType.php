<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


use Lci\BoilerBoxBundle\Form\Type\SiteConfigurationType;





class SiteConnexionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder                 
				->add('surveillance', CheckboxType::class, array(
                    'label'         => 'Avertissement par mail en cas de perte de connexion',
                    'required'      => false
                ))
				->add('url', TextType::class, array(
                    'label'         => 'Adresse',
                    'attr'          => array (
                        'placeholder'   => 'Adresse url',
                        'max_length'    => 255
                    ),
                    'required'      => true,
                    'trim'          => true
                ))
               ->add('codeLive', TextType::class, array(
                    'label'         => 'Code du live',
                    'attr'          => array (
                        'placeholder'   => 'Code du Live',
                        'max_length'    => 255
                    ),
                    'required'      => false,
                    'trim'          => true
                ))
                ->add('accesDistant', ChoiceType::class, array(
                    'label'         => 'Accès à distance possible (au minimum par ecatcher)',
                    'multiple'      => true,
                    'expanded'      => true,
                    'choices'       => array(
                        'Non'           => 'non',
                        'BoilerBox.fr'  => 'boilerbox',
                        'Ecatcher'      => 'ecatcher',
                        'Cloud'         => 'cloud',
                        'Live'          => 'live'
                    ),
                    'attr'          => array('class' => 'radio_smalltext')
                ))
                ->add('typeConnexion', ChoiceType::class, array(
                    'label'         => "Connexion vers l'ipc distant",
                    'multiple'      => true,
                    'expanded'      => true,
                    'choices'       => array(
                        '3G / 4G'       => '3g4g',
                        'Adsl'          => 'adsl'
                    ),
                    'attr'          => array('class' => 'radio_smalltext')
                ))
                ->add('submit', SubmitType::class,  ['label' => 'Save', 'attr' => ['class' => 'cacher']]);

    }

	/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\SiteConnexion'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lci_boilerboxbundle_siteconnexion';
    }


}
