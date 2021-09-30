<?php
namespace Lci\BoilerBoxBundle\Entity;

class GenerateurMdp
{
	/**
	 * @var string
	*/
	protected $duree;

	/**
	 * @var \DateTime
	*/
	protected $ladate;

	/**
	 * @var \DateTime
	*/
	protected $lheure;

	/**
	 * @var string
	*/
	protected $niveau;


	/**
	 * @var string
	*/
	protected $site;


	public function __construct()
	{

		$this->duree = 'heure';
        $this->ladate = new \Datetime();
        $this->lheure = new \Datetime();
		$this->niveau = 60;
		$this->site = 'C000';
	}


	/**
     * Get duree
     *
     * @return string
    */
	public function getDuree()
	{
		return $this->duree;
	}
    /**
     * Set duree
     *
     * @param string
	 * @return GenerateurMdp
    */
    public function setDuree($duree)
    {
		$this->duree = $duree;

        return $this;
    }


    /**
     * Get ladate
     *
     * @return string
    */
    public function getLadate()
    {
        return $this->ladate;
    }
    /**
     * Set ladate
     *
     * @param string
     * @return GenerateurMdp
    */
    public function setLadate($ladate)
    {
		$this->ladate = $ladate;

        return $this;
    }


    /**
     * Get lheure
     *
     * @return string
    */
    public function getLheure()
    {
        return $this->lheure;
    }
    /**
     * Set lheure
     *
     * @param string
     * @return GenerateurMdp
    */
    public function setLheure($lheure)
    {
		$this->lheure = $lheure;

        return $this;
    }



    /**
     * Get niveau
     *
     * @return string
    */
    public function getNiveau()
    {
        return $this->niveau;
    }
    /**
     * Set niveau
     *
     * @param string
     * @return GenerateurMdp
    */
    public function setNiveau($niveau)
    {
        $this->niveau= $niveau;

        return $this;
    }


    /**
     * Get site
     *
     * @return string
    */
    public function getSite()
    {
        return $this->site;
    }
    /**
     * Set site
     *
     * @param string
     * @return GenerateurMdp
    */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }


}
