<?php
namespace App\Entity;

use App\Entity\Traits\CreationTrait;
use App\Entity\Traits\SuppressionTrait;
use App\Entity\Traits\UpdateTrait;
use c975L\UserBundle\Entity\UserLightAbstract;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user", indexes={@ORM\Index(name="un_email", columns={"name", "email"})})
 * @ORM\Entity(repositoryClass="c975L\UserBundle\Repository\UserRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class User extends UserLightAbstract
{
    use CreationTrait;
    use UpdateTrait;
    use SuppressionTrait;

    /**
     * @ORM\OneToOne(targetEntity="UserPersonLink", mappedBy="user")
     */
    private $userPersonLink;

    public function getUserPersonLink(): ?UserPersonLink
    {
        return $this->userPersonLink;
    }

    public function setUserPersonLink(?UserPersonLink $userPersonLink): self
    {
        $this->userPersonLink = $userPersonLink;

        return $this;
    }
}
