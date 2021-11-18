<?php
namespace Lci\BoilerBoxBundle\Form\Type;
// Lci/BoilerBox/Form/Type/RegistrationFormType.php

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationFormType extends BaseType
{
	protected $em;

    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class, EntityManager $em)
    {
        $this->class = $class;
		$this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
			->add('roles', ChoiceType::class, array(
					'label'     => 'Rôles',
					'choices' => $this->fillRoles(),
					'expanded'  => false,
            		'multiple' => true,
					'attr' => array(
                		'placeholder'   => 'Rôle'
            		)
				))
                ->add('nom', TextType::class, array(
                        'label'     => 'Nom',
                        'attr'      => array(
                            'placeholder'   => 'nom',
                            'max_length'=> '35'
                        ),
                        'required'  => true,
                        'trim'      => true
                ))
                ->add('prenom', TextType::class, array(
                        'label'     => 'Prénom',
                        'attr'      => array(
                            'placeholder'   => 'prénom',
                            'max_length'=> '35'
                        ),
                        'required'  => true,
                        'trim'      => true
                ))
                ->add('username', null, array(
                    'label'              => 'Identifiant', 
                    'translation_domain' => 'FOSUserBundle',
                    'attr'=> array (
                        'placeholder'   => 'Identifiant utilisateur'
                    )
                ))
                ->add('email', EmailType::class, array(
                    'label' => 'Adresse mail', 
                    'translation_domain' => 'FOSUserBundle',
                    'attr'=> array (
                        'placeholder'   => 'adresse mail'
                    )
                ))
                ->add('plainPassword', RepeatedType::class, array(
                    'type'              => PasswordType::class,
                    'options'           => array(
						'translation_domain' => 'FOSUserBundle',
						'attr' => array(
                        	'autocomplete' => 'new-password'
                    	),
					),
                    'first_options'     => array(
                        'label' => 'Nouveau mot de passe',
                        'attr'  => array (
                            'placeholder'   => 'Nouveau mot de passe'
                        )
                    ),
                    'second_options'    => array(
                        'label' => 'Vérification du nouveau mot de passe',
                        'attr'  => array (
                            'placeholder'   => 'Vérification du nouveau mot de passe'
                        )
                    ),
                    'invalid_message'   => 'fos_user.password.mismatch'
                ))
				->add('enabled', CheckboxType::class, array(
						'label' => 'Activation du compte'
				))
				->add('totpKey', TextType::class, array(
					'label' => false,
					'attr' => array(
						'class' => 'cacher'
					)
				))
                ->add('label', TextType::class, array(
                        'label'     => 'Pseudo',
                        'attr'      => array(
                            'placeholder'   => 'pseudo',
                            'class'         => 'input_txt_large',
                            'max_length'=> '35'
                        ),
                        'required'  => true,
                        'trim'      => true
                ))
				->add('couleur', TextType::class, array(
					'label' => false,
					'attr' => array (
						'class' => 'cacher'
					)
				))
				->add('langue', ChoiceType::class, array(
					'label' => 'langue',
					'choices' => array(
						'fr' => 'fr',
						'en' => 'en',
						'es' => 'es',
						'de' => 'de'
					)
				))
				->add('modePrive', CheckboxType::class, array(
                        'label' => 'Activation du mode privé'
                ))
				->add('informations', TextAreaType::class, array(
					'label' => false
                ));
    }

    public function getParent() {
	    return BaseType::class;
    }


    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\User',
			'csrf_token_id' => 'registration',
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lci_user_registration';
    }


	// Fonction qui retourne les rôles définis en base de donnée
	private function fillRoles() {
		$tableau_des_roles = array();
		$tab_roles = $this->em->getRepository('LciBoilerBoxBundle:Role')->recuperationDesRoles();
		foreach($tab_roles as $key => $sous_tab_roles) {
			foreach ($sous_tab_roles as $key2 => $role) {
				//$tableau_des_roles[$role] = strtolower(substr($role, 5));
				$tableau_des_roles[strtolower(substr($role, 5))] = $role;
				
			}
		}
		asort($tableau_des_roles);
		return ($tableau_des_roles);
	}
}
