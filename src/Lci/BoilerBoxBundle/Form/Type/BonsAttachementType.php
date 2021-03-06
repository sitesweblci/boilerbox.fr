<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



class BonsAttachementType extends AbstractType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $option) {
		$builder
		->add('id', IntegerType::class, array(
			'label'				=> false,
			'attr'				=> array(
				'class'				=> 'cacher'
			)
		))
        ->add('userInitiateur', EntityType::class, array (
            'class'           => 'LciBoilerBoxBundle:User',
            'required'        => true,
            'label'           => 'Initiateur',
            'label_attr'      => array ('class' => 'label_smalltext'),
            'choice_label'    => 'label',
            'query_builder'   => function(EntityRepository $er){
					return $er->createQueryBuilder('u')
						->where('u.roles LIKE :role')
						->andWhere('u.enabled = :enabled')
						->setParameter('role', '%ROLE_SAISIE_BA%')
						->setParameter('enabled', true)
			   			->orderBy('u.label', 'ASC');
            },
        ))
        ->add('user', EntityType::class, array (
            'class'           => 'LciBoilerBoxBundle:User',
            'label'           => 'Intervenant',
            'label_attr'      => array ('class' => 'label_smalltext'),
			'choice_label'    => 'label',
            'attr'            => array(
                'class'             => 'jq-select-2'

            ),
			'query_builder'   => function(EntityRepository $er){
				return $er->createQueryBuilder('u')
					->where('u.roles LIKE :role')
					->andWhere('u.enabled = :enabled')
        			->setParameter('role', '%ROLE_INTERVENANT_BA%')
					->setParameter('enabled', true)
					->orderBy('u.label', 'ASC');
			},
			'placeholder'		=> "A d??finir"
        ))
        ->add('dateInitialisation', DateType::class, array(
            'label'         => 'Date d\'initialisation',
            'label_attr'    => array ('class' => 'label_smalltext'),
            'widget'        => 'single_text',
            'html5'         => false,
            'format'        => 'dd/MM/yyyy',
            'invalid_message' => 'Format de la date incorrect.',
            'attr'          => array(
                'placeholder'   => 'dd/mm/YYYY',
                'maxlength'     => 10,
				'class'			=> ''
            )
        ))
/*
		->add('numeroBA', TextType::class, array(
			'label' 		=> 'Num??ro du bon',
			'label_attr'    => array ('class' => 'label_smalltext'),
			'required' 		=> true,
            'trim' 			=> true,
			'attr' 			=> array(
				'placeholder' 	=> 'XXXXXX',
				'maxlength'     => 6
			)
		))
*/
		->add('numeroAffaire', TextType::class, array(
            'label' 		=> 'Affectation comptable',
			'label_attr'    => array ('class' => 'label_smalltext'),
			'required' 		=> true,
            'trim' 			=> true,
            'attr' 			=> array(
				'placeholder'	=> '(C | D | G | V | PL | PC | SAL | SAC | COL |  COC)XXX',
				'class' 		=> 'biginput upper uppercase',
				'maxlength' 	=> 7
            )
        ))
        ->add('site', EntityType::class, array(
            'label'         => 'Site',
			'class'			=> 'LciBoilerBoxBundle:SiteBA',
			'choice_label'  => 'intitule',
			'placeholder'	=> 'Ajouter un nouveau site',
			'query_builder'	=> function (EntityRepository $er) {
				return $er->createQueryBuilder('ba')
					->orderBy('ba.intitule', 'ASC');
			},
			'required'		=> true,
            'label_attr'    => array ('class' => 'label_smalltext'),
        ))
		->add('dateSignature', DateType::class, array(
			'label' 		=> false,
			'widget' 		=> 'single_text',
			'html5'			=> false,
			'format'        => 'dd/MM/yyyy',
            'invalid_message' => 'Format de la date incorrect.',
			'attr' 			=> array(
				'placeholder' 	=> 'dd/mm/YYYY',
				'maxlength' 	=> 10,
                'class'         => 'cacher'
			),
            'required'      => false
		))
        ->add('dateDebutIntervention', DateType::class, array(
            'label'         => 'Date de d??but d\'intervention',
            'label_attr'    => array ('class' => 'label_smalltext'),
            'widget'        => 'single_text',
            'html5'         => false,
            'format'        => 'dd/MM/yyyy',
            'invalid_message' => 'Format de la date incorrect.',
            'attr'          => array(
                'placeholder'   => 'dd/mm/YYYY',
                'maxlength'     => 10,
                'class'         => ''
            ),
			'required'		=> false
        ))
        ->add('dateFinIntervention', DateType::class, array(
            'label'         => 'Date de fin d\'intervention',
            'label_attr'    => array ('class' => 'label_smalltext'),
            'widget'        => 'single_text',
            'html5'         => false,
            'format'        => 'dd/MM/yyyy',
            'invalid_message' => 'Format de la date incorrect.',
            'attr'          => array(
                'placeholder'   => 'dd/mm/YYYY',
                'maxlength'     => 10,
                'class'         => ''
            ),
            'required'      => false
        ))
		->add('nomDuContact', TextType::class, array(
			'label'			=> 'Nom du contact',
			'label_attr'    => array ('class' => 'label_smalltext'),
            'required'      => true,
            'trim'          => true,
            'attr'          => array(
				'placeholder'   => "Cr??ez un contact dans l'encart de droite et selectionnez celui ci",
            )
		))
		->add('emailContactClient', TextType::class, array(
			'label' 		=> 'Email du contact',
			'label_attr'    => array ('class' => 'label_smalltext'),
			'required' 		=> true,
            'trim' 			=> true,
			'attr' 			=> array ( 
				'placeholder' 	=> "Champs rempli automatiquement lors de la s??lection d'un contact dans l'encart de droite",
			),
            'required'      => false
		))
		->add('telephoneContactClient', TelType::class, array(
            'label'         => 'T??l??phone du contact',
            'label_attr'    => array ('class' => 'label_smalltext'),
            'required'      => true,
            'trim'          => true,
            'attr'          => array (
                'placeholder'   => "Champs rempli automatiquement lors de la s??lection d'un contact dans l'encart de droite",
            ),
            'required'      => false
        ))
		->add('fichiersPdf', CollectionType::class, array(
			'entry_type'    => FichierType::class,
			/* Option ?? ajouter pour r??soudre l'erreur -> Warning: spl_object_hash() expects parameter 1 to be object, array given */
            'entry_options'	=> array('data_class' => 'Lci\BoilerBoxBundle\Entity\Fichier'),
			'label' 		=> 'Fichier(s) pdf du bon',
			'label_attr'    => array ('class' => 'label_smalltext'),
			'allow_add'		=> true,
			'allow_delete'	=> true,
			'required' 		=> true
		))
		->add('service', ChoiceType::class, array(
			'label'         => 'Service',
			'placeholder'   => 'Tous',
			'choices'		=> [
				'Bosch'		=> 'bosch',
				'Certus'	=> 'certus',
				'Export'	=> 'export'
			],
			'required' => false
		))
		->add('typeIntervention', ChoiceType::class, array(
			'label'			=> "Type d'intervention",
            'choices'       => [
                'Mise en service'   => 'mes',
                'SAV'    			=> 'sav',
                'Rapport mensuel'   => 'rptmensuel'
            ]
        ))
		->add('type', TextType::class, array(
            'label'         => false,
            'required'      => true,
            'trim'          => true,
            'attr'          => array (
                'placeholder'   => "Type du formulaire (bon ou ticket)",
				'class'			=> 'cacher'
            )
        ))
        ->add('equipementBATicket', CollectionType::class, array(
            'entry_type'    => EquipementBATicketType::class,
            'entry_options' => array('data_class' => 'Lci\BoilerBoxBundle\Entity\EquipementBATicket'),
            'label'         => false,
            'label_attr'    => array ('class' => 'label_smalltext'),
            'allow_add'     => true,
            'allow_delete'  => true,
            'required'      => true
        ))
        ->add('cheminDossierPhotos', TextType::class, array(
            'label'         => 'Chemin local vers le r??pertoire des photos',
            'label_attr'    => array(
                'class'         => 'label_smalltext'
            ),
            'attr'          => array(
                'class'         => 'biginput ',
                'placeholder'   => "Chemin vers le repertoire local des photos",
                'style'         => 'width:100%;'
            ),
            'required'      => false
        ))
		->add('typeNouveau', TextType::class, array(
            'label'         => false,
            'required'      => false,
			'mapped'		=> false,
            'trim'          => true,
			'attr'			=> array(
				'class'			=> 'cacher' 
			)
        ))
		->add('idNouveau', IntegerType::class, array(
            'label'         => false,
            'required'      => false,
			'mapped'		=> false,
            'attr'          => array(
                'class'         => 'cacher'
            )
        ))
        ->add('siteNouveau', TextType::class, array(
            'label'         => false,
            'required'      => false,
            'mapped'        => false,
            'trim'          => true,
            'attr'          => array(
                'class'         => 'cacher'
            )
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
