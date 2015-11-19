<?php

namespace Manager\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Manager\Bundle\Entity\Company;
/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Manager\Bundle\Entity\Repository\UserRepository")
 */
class User implements UserInterface
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
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $last_name;
    
    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;
    
    /**
     * @var string
     * @ORM\Column(name="role", type="string", length=255)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="Company", mappedBy="creator")
     */
    private $companies;
    
    /**
     * @ORM\OneToMany(targetEntity="Log", mappedBy="user")
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="creator")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="Manager\Bundle\Entity\Document", mappedBy="user")
     */
    private $documents;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function eraseCredentials() {
        
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRoles() {
        return [$this->role];
    }

    public function getSalt() {
        return null;
    }

    public function getUsername() {
        return $this->email;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->companies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add companies
     *
     * @param \Manager\Bundle\Entity\Company $companies
     * @return User
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

    /**
     * Add logs
     *
     * @param \Manager\Bundle\Entity\Log $logs
     * @return User
     */
    public function addLog(\Manager\Bundle\Entity\Log $logs)
    {
        $this->logs[] = $logs;

        return $this;
    }

    /**
     * Remove logs
     *
     * @param \Manager\Bundle\Entity\Log $logs
     */
    public function removeLog(\Manager\Bundle\Entity\Log $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Add note
     *
     * @param \Manager\Bundle\Entity\Note $note
     *
     * @return User
     */
    public function addNote(\Manager\Bundle\Entity\Note $note)
    {
        $this->notes[] = $note;

        return $this;
    }

    /**
     * Remove note
     *
     * @param \Manager\Bundle\Entity\Note $note
     */
    public function removeNote(\Manager\Bundle\Entity\Note $note)
    {
        $this->notes->removeElement($note);
    }

    /**
     * Get notes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add document
     *
     * @param \Manager\Bundle\Entity\Document $document
     *
     * @return User
     */
    public function addDocument(\Manager\Bundle\Entity\Document $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * Remove document
     *
     * @param \Manager\Bundle\Entity\Document $document
     */
    public function removeDocument(\Manager\Bundle\Entity\Document $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
