<?php

namespace Manager\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manager\Bundle\Entity\Company;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Step
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Manager\Bundle\Entity\Repository\StepRepository")
 */
class Step
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="stepLvl", type="integer")
     */
    private $stepLvl;

    /**
     * @ORM\OneToMany(targetEntity="Company", mappedBy="step")
     */
    private $companies;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Step
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set stepLvl
     *
     * @param integer $stepLvl
     * @return Step
     */
    public function setStepLvl($stepLvl)
    {
        $this->stepLvl = $stepLvl;

        return $this;
    }

    /**
     * Get stepLvl
     *
     * @return integer 
     */
    public function getStepLvl()
    {
        return $this->stepLvl;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->companies = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add companies
     *
     * @param \Manager\Bundle\Entity\Company $companies
     * @return Step
     */
    public function addCompany(\Manager\Bundle\Entity\Company $companies)
    {
        $this->companies[] = $companies;

        return $this;
    }

    /**
     * Remove companies
     *
     * @param \Manager\Bundle\Entity\Company $companies
     */
    public function removeCompany(\Manager\Bundle\Entity\Company $companies)
    {
        $this->companies->removeElement($companies);
    }

    /**
     * Get companies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompanies()
    {
        return $this->companies;
    }
}
