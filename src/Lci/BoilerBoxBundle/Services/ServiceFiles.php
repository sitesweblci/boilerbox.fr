<?php
namespace Lci\BoilerBoxBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ServiceFiles {
	private $problemes_files_dir;

	public __construct($problemes_files_dir) {
		$this->problemes_files_dir = $problemes_files_dir;
	}

	public function upload(UploadedFile $file) {
		$nom_fichier = md5(uniqId()).'.'.$file->guessExtension();
		$file->move($this->problemes_files_dir, $nom_fichier);
		return $nom_fichier;
	}

	public function getProblemesFilesDir() {
		return $this->problemes_files_dir;
	}

	// Fonction qui créée un fichier csv et propose son téléchargement
	public function createCsvFile($lignes) {
		$chemin_sur_serveur = 
	}
}
