<?php
namespace Lci\BoilerBoxBundle\Services;


class ServiceLog {



public function getLogDir() {
    return __DIR__.'/../../../../web/logs/';
}


public function setLog($message, $nomFichier=null) 
{
	if ($nomFichier == null) {
		$nomFichier = "debug.log";
	}
    $ficlog = $this->getLogDir().$nomFichier;
    $message = date("d/m/Y;H:i:s;").$message."\n";
    $fp = fopen($ficlog,"a");
    fwrite($fp,$message);
    fclose($fp);
}



}
