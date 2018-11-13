<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Pickup;

/**
 * PickupVoter class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PickupVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const PERSON_CREATE = 'pickupCreate';
    public const PERSON_DELETE = 'pickupDelete';
    public const PERSON_DISPLAY = 'pickupDisplay';
    public const PERSON_LIST = 'pickupList';
    public const PERSON_MODIFY = 'pickupModify';
    public const PERSON_SEARCH = 'pickupSearch';

    private const ATTRIBUTES = array(
        self::PERSON_CREATE,
        self::PERSON_DELETE,
        self::PERSON_DISPLAY,
        self::PERSON_LIST,
        self::PERSON_MODIFY,
        self::PERSON_SEARCH,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Pickup && in_array($attribute, self::ATTRIBUTES);
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
            case self::PERSON_CREATE:
                return $this->canCreate($token, $subject);
                break;
            case self::PERSON_DELETE:
                return $this->canDelete($token, $subject);
                break;
            case self::PERSON_DISPLAY:
                return $this->canDisplay($token, $subject);
                break;
            case self::PERSON_LIST:
                return $this->canList($token, $subject);
                break;
            case self::PERSON_MODIFY:
                return $this->canModify($token, $subject);
                break;
            case self::PERSON_SEARCH:
                return $this->canSearch($token, $subject);
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }


    /**
     * Checks if is allowed to create
     */
    private function canCreate()
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

        return false;
    }

    /**
     * Checks if is allowed to delete
     */
    private function canDelete()
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

        return false;
    }

    /**
     * Checks if is allowed to display
     */
    private function canDisplay()
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

        return false;
    }

    /**
     * Checks if is allowed to list
     */
    private function canList()
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

        return false;
    }

    /**
     * Checks if is allowed to modify
     */
    private function canModify()
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

        return false;
    }
}