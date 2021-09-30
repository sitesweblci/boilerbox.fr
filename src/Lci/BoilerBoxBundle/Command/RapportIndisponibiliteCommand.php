<?php
// src/Lci/BoilerBoxBundle/Command/RapportIndisponibiliteCommand.php
namespace Lci\BoilerBoxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RapportIndisponibiliteCommand extends ContainerAwareCommand {
	protected function configure() {
		$this->setName('boilerbox:rapportIndisponibilite')
    	     ->setDescription("Envoi du rapport d'indisponibilite");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$service_rapport = $this->getContainer()->get('lci_boilerbox.rapport');
		$service_rapport->setRapportDisponibilite();
		return("Fin d'analyse des indisponibilités boiler-box");
	}
}
