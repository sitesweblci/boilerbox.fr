<?php
// src/Lci/BoilerBoxBundle/Command/UtilitairesCommand.php
namespace Lci\BoilerBoxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UtilitairesCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('boilerbox:utils')
            ->setDescription('Execution des utilitaires');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getApplication()->getKernel();
        $doctrine = $kernel->getContainer()->get('doctrine');
        $projet_dir = $kernel->getRootDir() . '/../';

        $sites = $doctrine->getRepository('LciBoilerBoxBundle:Site')->findAll();
        $i = 0;
        $runningProcesses = [];
        foreach ($sites as $site) {
            $i++;
            if ($i % 10 === 0) {
                sleep(6);
            }
            $process = new Process('php ' . $projet_dir.'bin/console boilerbox:ewons ' . $site->getAffaire());
            $process->start();
            $runningProcesses[] = $process;
        }

        while (count($runningProcesses)) {
            foreach ($runningProcesses as $i => $runningProcess) {
                // Si un proccess est fini on le remove
                if (!$runningProcess->isRunning()) {
                    unset($runningProcesses[$i]);
                }
            }
            // on vérifie chaque seconde
            sleep(1);
        }

        return ("Fin d'analyse de la disponibilité des sites");
    }

}
