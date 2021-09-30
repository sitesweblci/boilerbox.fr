<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormbuilderInterface;
use Lci\BoilerBoxBundle\Entity\SiteRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ListeDesSitesType extends AbstractType 
{
  public function buildForm(FormBuilderInterface $builder, array $options) 
  {
		$builder
		  ->add('site', EntityType::class, array(
			'label' 		=> 'Site',
			'class' 		=> 'Lci\BoilerBoxBundle\Entity\Site',
			'choice_label' 	=> 'intitule',
			'query_builder' => function (SiteRepository $sr) {
                return $sr->createQueryBuilder('s')->orderBy('s.intitule', 'ASC');
			},
			'mapped'	=> false
			))
		  ->add('submit', 'submit');
  }

  public function getName()
  {
	return 'ListeDesSites';
  }
}
