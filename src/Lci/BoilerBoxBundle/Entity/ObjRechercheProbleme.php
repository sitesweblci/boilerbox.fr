<?php
//src/Lci/BoierBoxBundle/Entity/ObjetRechercheProbleme.php

namespace Lci\BoilerBoxBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping as ORM;


/**
 * ObjRechercheProbleme
 *
*/
class ObjRechercheProbleme
{

    /**
     * @Assert\Date()
     * 
    */
    protected $dateDebut;

    /**
     * @Assert\Date()
     *
    */
    protected $dateFin;
	protected $intervenantId;
	protected $moduleId;
	protected $equipementId;

	protected $corrige;
	protected $nonCorrige;

	protected $cloture;
	protected $nonCloture;

	protected $bloquant;
	protected $nonBloquant;

	protected $present;
	protected $nonPresent;

	protected $reference;

	protected $type;

    public function setReference($reference) {
		$this->reference = $reference;
		return $this;
	}

    public function getReference() {
        return $this->reference;
    }


    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getType() {
        return $this->type;
    }



    public function setBloquant($bloquant) {
		$this->bloquant = $bloquant;
		return $this;
	}

    public function getBloquant() {
        return $this->bloquant;
    }

    public function setNonBloquant($bloquant) {
        $this->nonBloquant = $bloquant;
        return $this;
    }

    public function getNonBloquant() {
        return $this->nonBloquant;
    }



    public function setCorrige($correction) {
		$this->corrige = $correction;
		return $this;
	}

    public function getCorrige() {
        return $this->corrige;
    }

    public function setNonCorrige($correction) {
        $this->nonCorrige = $correction;
        return $this;
    }

    public function getNonCorrige() {
        return $this->nonCorrige;
    }



    public function setCloture($cloture) {
		$this->cloture = $cloture;
		return $this;
	}

    public function getCloture() {
        return $this->cloture;
    }

    public function setNonCloture($cloture) {
        $this->nonCloture = $cloture;
        return $this;
    }

    public function getNonCloture() {
        return $this->nonCloture;
    }




    public function setPresent($present) {
		$this->present = $present;
		return $this;
	}

    public function getPresent() {
        return $this->present;
    }

    public function setNonPresent($present) {
        $this->nonPresent = $present;
        return $this;
    }

    public function getNonPresent() {
        return $this->nonPresent;
    }




    /**
     * Set dateDebut
     *
     * @return ObjRechercheProbleme
    */
	public function setDateDebut($nouvelleDate){
		$this->dateDebut = $nouvelleDate;
        return $this;
	}

    /**
     * Get dateDebut
     *
     * @return date
    */
    public function getDateDebut(){
		return $this->dateDebut;
    }


    /**
     * Set dateFin
     *
     * @return ObjRechercheProbleme
    */
    public function setDateFin($nouvelleDate){
        $this->dateFin = $nouvelleDate;
        return $this;
    }

    /**
     * Get dateFin
     *
     * @return date
    */
    public function getDateFin(){
		return $this->dateFin;
    }


    /**
     * Set intervenantId
     *
     * @return ObjRechercheProbleme
    */
    public function setIntervenantId($intervenantId){
		$this->intervenantId = $intervenantId;
		return $this;
    }

    /**
     * Get intervenantId
     *
     * @return integer
    */
    public function getIntervenantId(){
		return $this->intervenantId;
    }


    /**
     * Set moduleId
     *
     * @return ObjRechercheProbleme
    */
    public function setModuleId($moduleId){
		$this->moduleId = $moduleId;
        return $this;
    }

    /**
     * Get module
     *
     * @return integer
    */
    public function getModuleId(){
		return $this->moduleId;
    }

    /**
     * Set equipementId
     *
     * @return ObjRechercheProbleme
    */
    public function setEquipementId($equipementId){
                $this->equipementId = $equipementId;
        return $this;
    }

    /**
     * Get equipement
     *
     * @return integer
    */
    public function getEquipementId(){
                return $this->equipementId;
    }

	
}
