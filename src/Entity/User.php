<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use c975L\UserBundle\Entity\UserLightAbstract;

/**
 * @ORM\Table(name="user", indexes={@ORM\Index(name="un_email", columns={"name", "email"})})
 * @ORM\Entity(repositoryClass="c975L\UserBundle\Repository\UserRepository")
 *
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class User extends UserLightAbstract
{
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
