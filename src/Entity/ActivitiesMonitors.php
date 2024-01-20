<?php

namespace App\Entity;

use App\Repository\ActivitiesMonitorsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActivitiesMonitorsRepository::class)
 */
class ActivitiesMonitors
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Activity::class, inversedBy="activitiesMonitors")
     */
    private $activityMonitors;

    public function __construct()
    {
        $this->activityMonitors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ActivityMonitor>
     */
    public function getActivityMonitors(): Collection
    {
        return $this->activityMonitors;
    }

    public function addActivityMonitor(ActivitiesMonitors $activityMonitors): self
    {
        if (!$this->activityMonitors->contains($activityMonitors)) {
            $this->activityMonitors[] = $activityMonitors;
        }

        return $this;
    }

    public function removeActivityMonitor(ActivitiesMonitors $activityMonitors): self
    {
        $this->activityMonitors->removeElement($activityMonitors);

        return $this;
    }
}
