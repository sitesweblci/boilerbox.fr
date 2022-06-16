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
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;



class EquipementBATicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('id', IntegerType::class, array(
            	'label'             => false,
            	'attr'              => array(
            	    'class'             => 'cacher'
            	)
        	))
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
				'attr'			=> array(
					'style'		=> 'text-transform:capitalize;'
				)
        	))
            ->add('autreDenomination', TextType::class, array(
        	    'label'         => 'Autre dénomination',
        	    'label_attr'    => array ('class' => 'label_smalltext'),
        	    'required'      => true,
        	    'trim'          => true,
				'attr'          => array(
                    'style'     => 'text-transform:capitalize;'
                )
        	))
            ->add('anneeDeConstruction', DateType::class, array(
            	'label'         => 'Année de construction',
            	'label_attr'    => array ('class' => 'label_smalltext'),
            	'widget'        => 'single_text',
            	'html5'         => false,
            	'format'        => 'yyyy/MM/dd',
            	'invalid_message' => 'Format de la date incorrect.',
            	'attr'          => array(
            	    'placeholder'   => 'yy/MM/dd',
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
			->add('reset', ResetType::class, [
				'attr' => [
					'class' => 'cacher'
				]
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EquipementBATicket::class,
        ]);
    }
}
