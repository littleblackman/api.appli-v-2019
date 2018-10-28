<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Address;

class AddressVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const ADDRESS_CREATE = 'addressCreate';
    public const ADDRESS_DELETE = 'addressDelete';
    public const ADDRESS_DISPLAY = 'addressDisplay';
    public const ADDRESS_MODIFY = 'addressModify';

    private const ATTRIBUTES = array(
        self::ADDRESS_CREATE,
        self::ADDRESS_DELETE,
        self::ADDRESS_DISPLAY,
        self::ADDRESS_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Address && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Checks current user
        if (null === $token->getUser() || is_string($token->getUser())) {
            return false;
        }

        //Defines access rights
        switch ($attribute) {
            case self::ADDRESS_CREATE:
                return $this->canCreate($token, $subject);
                break;
            case self::ADDRESS_DELETE:
                return $this->canDelete($token, $subject);
                break;
            case self::ADDRESS_DISPLAY:
                return $this->canDisplay($token, $subject);
                break;
            case self::ADDRESS_MODIFY:
                return $this->canModify($token, $subject);
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }


    /**
     * Checks if is allowed to create
     */
    private function canCreate($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
            'ROLE_LEADER',
            'ROLE_ADMIN',
        );

        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }

        //Checks roles UNallowed
        $roles = array(
            'ROLE_TRAINEE',
            'ROLE_COACH',
            'ROLE_DRIVER',
            'ROLE_ASSISTANT',
            'ROLE_MANAGER',
        );

        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return false;
            }
        }

        return $this->security->isGranted('ROLE_USER');
    }

    /**
     * Checks if is allowed to delete
     */
    private function canDelete($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
            'ROLE_LEADER',
            'ROLE_ADMIN',
        );

        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }

        return $this->isLinked($token, $subject);
    }

    /**
     * Checks if is allowed to display
     */
    private function canDisplay($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
            'ROLE_DRIVER',
            'ROLE_ASSISTANT',
            'ROLE_MANAGER',
            'ROLE_LEADER',
            'ROLE_ADMIN',
        );

        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }

        return $this->isLinked($token, $subject);
    }

    /**
     * Checks if is allowed to modify
     */
    private function canModify($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
            'ROLE_MANAGER',
            'ROLE_LEADER',
            'ROLE_ADMIN',
        );

        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }

        return $this->isLinked($token, $subject);
    }

    /**
     * Checks if child is linked to the user
     */
    public function isLinked($token, $subject)
    {
        if (null !== $token->getUser()->getUserPersonLink()) {
            $personId = $token->getUser()->getUserPersonLink()->getPerson()->getPersonId();
            foreach ($subject->getPersons() as $person) {
                if ($person->getPerson()->getPersonId() === $personId) {
                    return true;
                }
            }

            return false;
        }
    }
}
