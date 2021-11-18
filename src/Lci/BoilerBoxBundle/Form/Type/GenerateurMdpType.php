<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\OptionsResolver\OptionsResolver;


class GenerateurMdpType extends AbstractType 
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('duree', ChoiceType::class, [
				'choices' => [
					'Pour une heure' => 'heure',
					'Pour une journée' => 'jour'	
				],
				'expanded' => true	
			])
			->add('ladate', DateType::class, [
				'widget' => 'single_text'
			])
			->add('lheure', TimeType::class, [
				'widget' => 'single_text'
			])
			->add('niveau', ChoiceType::class, [
                'choices' => [
                    '20 - Exploitant base' => 20,
                    '40 - Exploitant avancé' => 40,
					'60 - Technicien constructeur' => 60
                ]
            ])
			->add('site', TextType::class);
	}

    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\GenerateurMdp'
        ));
	}
/*
	public function setDefaultsOptions(OptionResolverInterface $resolver) 
	{
	    $resolver->setDefaults(array('data_class' => 'Lci\BoilerBoxBundle\Entity\GenerateurMdp'));
	}
 */

	public function getName() 
	{
	    return 'GenerateurMdp';
	}

}
