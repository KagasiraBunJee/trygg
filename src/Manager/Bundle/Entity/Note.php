<?php

namespace Manager\Bundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Note
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Manager\Bundle\Entity\Repository\NoteRepository")
 */
class Note
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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Company",inversedBy="notes")
     * @ORM\JoinColumn(name="companyId", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="notes")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    private $creator;

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
     * Set title
     *
     * @param string $title
     *
     * @return Note
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Note
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Note
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set company
     *
     * @param \Manager\Bundle\Entity\Company $company
     *
     * @return Note
     */
    public function setCompany(\Manager\Bundle\Entity\Company $company = null)
    {
        $company->addNote($this);
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Manager\Bundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set creator
     *
     * @param \Manager\Bundle\Entity\User $creator
     *
     * @return Note
     */
    public function setCreator(\Manager\Bundle\Entity\User $creator = null)
    {
        $creator->addNote($this);
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \Manager\Bundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }
}
