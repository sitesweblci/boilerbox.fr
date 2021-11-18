<?php
// src/Lci/BoilerBoxBundle/Command/RelanceSynchroCommand.php
namespace Lci\BoilerBoxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RelanceSynchroCommand extends ContainerAwareCommand {

protected function configure() {
	$this	->setName('boilerbox:relanceSynchro')
			->setDescription('Relance de la synchronisation avec les masters');
	// Arguments necessaire pour la commande : Site sur lequels faire les actions : boilerbox ou lts-boilerbox
	$this
        ->addArgument('site_boilerbox', InputArgument::OPTIONAL, 'Site cible de la relance de synchronisation')
    ;
}

protected function execute(InputInterface $input, OutputInterface $output) 
{
	echo "\n*****\n";
	echo "Demande de relance de la synchronisation reçue\n";
	echo "*****\n";

	$site_boilerbox = $input->getArgument('site_boilerbox');	

	$service_utilitaires = $this->getContainer()->get('lci_boilerbox.synchronisation');
	echo $service_utilitaires->relanceSynchro($site_boilerbox);
	echo "\n";
	echo "\n";
	return 0;
}

}
