<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;


// Formulaire pour suppression d'une configuration supplémentaire

class SiteConfigurationPourSuppressionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
	    $builder
				->add('siteConfiguration', EntityType::class, array (
                    'class'             => 'LciBoilerBoxBundle:SiteConfiguration',
                    'label'             => 'Configuration à supprimer',
                    'choice_label'      => 'Configuration.parametre',
                    'query_builder'     => function(EntityRepository $er) use ($options)
                    {
                        return $er->createQueryBuilder('sc')
								->join('sc.configuration', 'c')
                                ->where('sc.site = :site')
                                ->setParameter('site', $options['site_id'])
								->orderBy('c.parametre', 'ASC');
                    },
                    'mapped'            => false
                ))

				->add('submit', SubmitType::class,  ['label' => 'Supprimer']);
    }




    /*
     * @param OptionsResolver $resolver
    */
	public function configureOptions(OptionsResolver $resolver)
	{
	    $resolver->setDefaults(array(
	        'site_id' => null,
			'data_class' => null
	    ));
	}

}
