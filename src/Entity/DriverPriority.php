<?php

namespace App\Entity;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Entity\Driver;

/**
 * DriverPriority
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPriority
{
    /**
     * @var string|null
     */
    private $driver;

    /**
     * @var integer
     */
    private $priority;


    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
