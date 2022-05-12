<?php

namespace Lci\BoilerBoxBundle\Form;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipementBATicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroDeSerie')
            ->add('denomination')
            ->add('autreDenomination')
            ->add('anneeDeConstruction')
            ->add('siteBA')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EquipementBATicket::class,
        ]);
    }
}
