<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * link
 *
 * @ORM\Table(name="link")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LinkRepository")
 */
class Link
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"link"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="original_link", type="string", length=255)
     * @Groups({"link","link_stat"})
     */
    private $originalLink;

    /**
     * @var string
     *
     * @ORM\Column(name="short_link", type="string", length=255, unique=true)
     * @Groups({"link"})
     */
    private $shortLink;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="life_time", type="datetime", nullable=true)
     * @Groups({"link", "link_stat"})
     */
    private $lifeTime;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     * @Groups({"link"})
     */
    private $isActive;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     * @Groups({"link_stat"})
     */
    private $creator;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Visit", mappedBy="link")
     * @Groups({"link_stat"})
     */
    private $visits;



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set originalLink
     *
     * @param string $originalLink
     *
     * @return link
     */
    public function setOriginalLink($originalLink)
    {
        $this->originalLink = $originalLink;

        return $this;
    }

    /**
     * Get originalLink
     *
     * @return string
     */
    public function getOriginalLink()
    {
        return $this->originalLink;
    }

    /**
     * Set shortLink
     *
     * @param string $shortLink
     *
     * @return link
     */
    public function setShortLink($shortLink)
    {
        $this->shortLink = $shortLink;

        return $this;
    }

    /**
     * Get shortLink
     *
     * @return string
     */
    public function getShortLink()
    {
        return $this->shortLink;
    }

    /**
     * Set lifeTime
     *
     * @param \DateTime $lifeTime
     *
     * @return link
     */
    public function setLifeTime($lifeTime)
    {
        $this->lifeTime = $lifeTime;

        return $this;
    }

    /**
     * Get lifeTime
     *
     * @return \DateTime
     */
    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return link
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->visits = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set creator
     *
     * @param \AppBundle\Entity\User $creator
     *
     * @return Link
     */
    public function setCreator(\AppBundle\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \AppBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Add visit
     *
     * @param \AppBundle\Entity\Visit $visit
     *
     * @return Link
     */
    public function addVisit(\AppBundle\Entity\Visit $visit)
    {
        $this->visits[] = $visit;

        return $this;
    }

    /**
     * Remove visit
     *
     * @param \AppBundle\Entity\Visit $visit
     */
    public function removeVisit(\AppBundle\Entity\Visit $visit)
    {
        $this->visits->removeElement($visit);
    }

    /**
     * Get visits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVisits()
    {
        return $this->visits;
    }

    public function __sleep()
    {
        return array('id', 'originalLink', 'shortLink');
    }
}
