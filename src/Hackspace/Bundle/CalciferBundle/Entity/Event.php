<?php

namespace Hackspace\Bundle\CalciferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Event
 *
 * @ORM\Table(name="events")
 * @ORM\Entity
 */
class Event extends BaseEntity
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startdate", type="datetimetz")
     */
    protected $startdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="enddate", type="datetimetz", nullable=true)
     */
    protected $enddate;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=255)
     */
    protected $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="locations_id", type="integer", nullable=true)
     */
    protected $locations_id;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="locations_id", referencedColumnName="id")
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var array
     *
     * @ORM\ManyToMany(targetEntity="Tag")
     * @ORM\JoinTable(name="events2tags",
     *      joinColumns={@ORM\JoinColumn(name="events_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tags_id", referencedColumnName="id")}
     *      )
     */
    protected $tags = [];

    /**
     * @param \Hackspace\Bundle\CalciferBundle\Entity\Location $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return \Hackspace\Bundle\CalciferBundle\Entity\Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function getTags() {
        return $this->tags;
    }

    public function hasTag(Tag $tag) {
        if ($this->tags instanceof PersistentCollection) {
            return $this->tags->contains($tag);
        } elseif (is_array($this->tags)) {
            return in_array($tag,$this->tags);
        } else {
            return false;
        }

    }

    public function addTag(Tag $tag) {
        /** @var PersistentCollection $this->tags */
        if (!$this->hasTag($tag)) {
            $this->tags[] = $tag;
        }
    }

    public function isValid() {
        return true;
    }

    public function getTagsAsText() {
        if (count($this->tags) > 0) {
            $tags = [];
            foreach ($this->tags as $tag) {
                $tags[] = $tag->name;
            }
            return implode(',',$tags);
        } else {
            return '';
        }
    }
}
