<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use AppBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TaskRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="street", type="string", length=255)
     */
    private $street;


    /**
     * @ORM\Column(name="house_number", type="string", length=32)
     */
    private $houseNumber;


    /**
     * @ORM\Column(name="post_code", type="string", length=64)
     */
    private $postCode;

    /**
     * @ORM\Column(name="place", type="string", length=64)
     */
    private $place;

    /**
     * @ORM\Column(name="task", type="string", length=64)
     */
    private $task;

    /**
     * @ORM\Column(name="comments", type="text")
     */
    private $comments;

    /**
     * @ORM\Column(name="valuation", type="string", length=64, nullable=true)
     */
    private $valuation;

    /**
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="task", fileNameProperty="image")
     */
    private $imageFile;

    /**
     * @ORM\Column(name="nr_plot", type="string", length=64)
     */
    private $nrPlot;

    /**
     * @ORM\Column(name="term", type="string", length=128, nullable=true)
     */
    private $term;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(name="valuation_comments", type="text", nullable=true)
     */
    private $valuationComments;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="published_at", type="datetime")
     */
    private $publishedAt;

    /**
     * @ORM\Column(name="updated_at",type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }


    public function getPostCode()
    {
        return $this->postCode;
    }

    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getValuation()
    {
        return $this->valuation;
    }

    public function setValuation($valuation)
    {
        $this->valuation = $valuation;
    }

    public function setImage($image = null)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImageFile(File $imageFile)
    {
        $this->imageFile = $imageFile;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setNrPlot($nrPlot)
    {
        $this->nrPlot = $nrPlot;
    }

    public function getNrPlot()
    {
        return $this->nrPlot;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getValuationComments()
    {
        return $this->valuationComments;
    }

    public function setValuationComments($valuationComments)
    {
        $this->valuationComments = $valuationComments;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}
