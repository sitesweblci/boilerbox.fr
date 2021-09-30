<?php
//src/Lci/BoilerBoxBundle/Services/ServiceConnexion.php
namespace Lci\BoilerBoxBundle\Services;

use \PDO;
use \PDOException;

class ServiceConnexion {
	private $dbh;

	public function __construct() {
      	try {
       	    // Paramètre locaux 
			$this->dbh = new PDO('mysql:host=127.0.0.1;dbname=boilerbox', 'cargo', 'adm5667');
			// Paramètre boilerbox $this->dbh = new PDO('mysql:host=127.0.0.1;dbname=boilerbox', 'symfony', 'adm5667');
       	} catch (PDOException $e) {
       	    echo $e->getMessage();
		    $this->dbh = null;
       	}
	}

	public function getDbh() {
		return $this->dbh;
	}

	public function disconnect() {
		$this->dbh = null;
		return(null);
	}
}
