<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\ORM\EntityRepository;
use Lci\BoilerBoxBundle\Form\Type\FichierType;



class BonsAttachementModificationType extends AbstractType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $option) {
		$builder
		->add('fichiersPdf', CollectionType::class, array(
			'entry_type'    => FichierType::class,
			/* Option à ajouter pour résoudre l'erreur -> Warning: spl_object_hash() expects parameter 1 to be object, array given */
            'entry_options'	=> array('data_class' => 'Lci\BoilerBoxBundle\Entity\Fichier'),
			'label_attr'    => array ('class' => 'label_smalltext'),
			'allow_add'		=> true,
			'allow_delete'	=> true
		));
	}


    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\BonsAttachement'
        ));
    }


    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_bonsAttachement';
    }

}
