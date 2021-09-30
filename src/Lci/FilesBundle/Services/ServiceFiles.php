<?php
namespace Lci\FilesBundle\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServiceFiles {
	private $chemin_dossier_files;
	private $service_template;

	public function __construct($templating, $chemin_dossier_files) {
		$this->service_template = $templating;
		// On s'assure que le chemin entré se termine par /
		$this->chemin_dossier_files = preg_replace('#([^/])$#', '$1/', $chemin_dossier_files);
		// On vérifie que le repertoire existe avec les bons droits
		if (! file_exists($this->chemin_dossier_files)) {
			throw new NotFoundHttpException('Le dossier '.$this->chemin_dossier_files.' n\'existe pas.');
		}
		if (! is_writable($this->chemin_dossier_files)) {
            throw new AccessDeniedHttpException('Le droits sur le dossier '.$this->chemin_dossier_files.' ne permettent pas la création de fichiers.');
        }
	}

	// Fonction qui créée un fichier csv et propose son téléchargement
	public function creerFichierCsv($entete = null, $lignes = null, $repertoire_du_fichier = '', $nom_du_fichier = null, $telechargement = true) {
		if(! isset($lignes) || ! isset($nom_du_fichier)) {
			throw new BadRequestHttpException('Les paramètres sont incorrects. Veuillez executer la méthode [ help ] pour plus de détails sur l\'utilisation du service.');
		}		

		$this->chemin_dossier_files .= $repertoire_du_fichier;
        $this->chemin_dossier_files = preg_replace('#([^/])$#', '$1/', $this->chemin_dossier_files);
        // On vérifie que le repertoire existe avec les bons droits
        if (! file_exists($this->chemin_dossier_files)) {
            throw new NotFoundHttpException('Le dossier '.$this->chemin_dossier_files.' n\'existe pas.');
        }
        if (! is_writable($this->chemin_dossier_files)) {
            throw new AccessDeniedHttpException('Le droits sur le dossier '.$this->chemin_dossier_files.' ne permettent pas la création de fichiers.');
        }
		// On préfix le nom du fichier par un identifiant php unique pour éviter l'écrasement des fichiers de même nom
        $this->chemin_dossier_files .= uniqid().'_'.date('dmY').'_';



		// Paramètrage du future fichier csv
		$nom_du_fichier .= '.csv';
		$chemin_fichiers_csv = $this->chemin_dossier_files.$nom_du_fichier;
		$delimiteur = ';';
		$ligne_entete = null;

		// Création et ouverture du fichier csv
		$fichier_csv = fopen($chemin_fichiers_csv, 'w+');
		
		// Pour pouvoir importer le fichier dans Excel : Correction des caractères internationaux (accents...)
		fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));	

        // Si le tableau des titres est définit on indique les titres en première ligne du fichier csv
        if(isset($entete)) {
			foreach($entete as $champ_entete) {
				$ligne_entete .= "$champ_entete;";
			}
			fputcsv($fichier_csv, $entete, $delimiteur);
        }


		// Ecriture du fichier
		foreach($lignes as $ligne) {
			fputcsv($fichier_csv, $ligne, $delimiteur);
		}

		// Fermeture du fichier
		fclose($fichier_csv);

		// Envoi du fichier si le paramètre telechargement est à true
		if ($telechargement == true) {
			$response = new Response();
    		$response->headers->set('Content-Type', 'application/force-download');
    		$response->headers->set('Content-Disposition', 'attachment;filename="'.$nom_du_fichier.'"');
    		$response->headers->set('Content-Length', filesize($this->chemin_dossier_files.$nom_du_fichier));
    		$response->setContent(file_get_contents($this->chemin_dossier_files.$nom_du_fichier));
    		$response->setCharset('UTF-8');
    		return $response;
		} else {
			return new Response($chemin_fichiers_csv);
		}
				
	}



	public function help() {
		return new Response($this->service_template->render('LciFilesBundle:Files:readme.html.twig'));
	}
}
