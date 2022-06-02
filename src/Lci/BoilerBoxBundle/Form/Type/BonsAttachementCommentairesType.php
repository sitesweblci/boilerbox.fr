<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementType as BaseType;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class BonsAttachementCommentairesType extends BaseType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		
		$builder->remove('numeroBA')
                ->remove('nomDuContact')
                ->remove('numeroAffaire')
                ->remove('cheminDossierPhotos')
				->remove('dateSignature')
                ->remove('dateInitialisation')
                ->remove('dateDebutIntervention')
                ->remove('dateFinIntervention')
				->remove('emailContactClient')
        		->remove('userInitiateur')
        		->remove('user')
        		->remove('site')
                ->remove('siteNouveau')
                ->remove('service')
        		->remove('fichiersPdf')
				->remove('idNouveau')
				->remove('equipementBATicket')
				->remove('telephoneContactClient')
				->remove('typeIntervention')
                ->remove('typeNouveau')
                ->remove('type')
				->add('commentaires', TextareaType::class, array(
            		'label'         => 'Commentaires ...',
            		'label_attr'    => array ('class' => 'label_bigtext'),
            		'attr'          => array(
                						'cols'      => 65,
                						'rows'      => 6,
                						'placeholder' => 'Nouveaux commentaires ...',
                						'style'     => 'resize:none;'
            							),
					'data'			=> ''	
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
