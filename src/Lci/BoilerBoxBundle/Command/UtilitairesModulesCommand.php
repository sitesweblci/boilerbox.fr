<?php
// src/Lci/BoilerBoxBundle/Command/UtilitairesModulesCommand.php
namespace Lci\BoilerBoxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UtilitairesModulesCommand extends ContainerAwareCommand {

protected function configure() {
	$this	->setName('boilerbox:modulesutils')
			->setDescription('Execution des utilitaires pour les modules');
}

protected function execute(InputInterface $input, OutputInterface $output) {
	$service_utilitaires = $this->getContainer()->get('lci_boilerbox.utilitaires');
	$service_utilitaires->analyseAccess('modules');
	return("Fin d'analyse de la disponibilité des modules");
}

}
