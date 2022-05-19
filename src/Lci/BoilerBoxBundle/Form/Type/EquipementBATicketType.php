<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class EquipementBATicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroDeSerie', TextType::class, array(
        	    'label'         => 'Numéro de série',
        	    'label_attr'    => array ('class' => 'label_smalltext'),
        	    'required'      => true,
        	    'trim'          => true,
        	    'attr'          => array(
        	        'placeholder'   => 'XXXXXX',
        	        'maxlength'     => 6
        	    )
        	))
            ->add('denomination', TextType::class, array(
        	    'label'         => 'Dénomination',
        	    'label_attr'    => array ('class' => 'label_smalltext'),
        	    'required'      => true,
        	    'trim'          => true,
        	))
            ->add('autreDenomination', TextType::class, array(
        	    'label'         => 'Autre dénomination',
        	    'label_attr'    => array ('class' => 'label_smalltext'),
        	    'required'      => true,
        	    'trim'          => true,
        	))
            ->add('anneeDeConstruction', DateType::class, array(
            	'label'         => 'Année de construction',
            	'label_attr'    => array ('class' => 'label_smalltext'),
            	'widget'        => 'single_text',
            	'html5'         => false,
            	'format'        => 'dd/MM/yyyy',
            	'invalid_message' => 'Format de la date incorrect.',
            	'attr'          => array(
            	    'placeholder'   => 'dd/mm/YYYY',
            	    'maxlength'     => 10,
					'class'			=> 'cacher'
            	)
        	))
            ->add('siteBA', EntityType::class, array(
            	'label'         => 'Site',
            	'class'         => 'LciBoilerBoxBundle:SiteBA',
            	'choice_label'  => 'intitule',
            	'placeholder'   => '',
            	'query_builder' => function (EntityRepository $er) {
            	    return $er->createQueryBuilder('ba')
            	        ->orderBy('ba.intitule', 'ASC');
            	},
            	'required'      => true,
            	'label_attr'    => array ('class' => 'label_smalltext'),
        	))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EquipementBATicket::class,
        ]);
    }
}
