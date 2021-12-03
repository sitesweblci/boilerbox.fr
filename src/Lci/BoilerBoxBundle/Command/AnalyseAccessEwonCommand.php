<?php
// src/Lci/BoilerBoxBundle/Command/UtilitairesModulesCommand.php
namespace Lci\BoilerBoxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseAccessEwonCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('boilerbox:ewons')
            ->addArgument('affaire', InputArgument::OPTIONAL, 'numéro de d\'affaire si on veut mettre à jour l\'état d\'une affaire');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $affaire = $input->getArgument('affaire');
        $service_utilitaires = $this->getContainer()->get('lci_boilerbox.utilitaires');
        $service_utilitaires->analyseAccess($affaire);

        return "Fin d'analyse de la disponibilité" . $affaire ? " de $affaire" : " des affaires";
    }
}
