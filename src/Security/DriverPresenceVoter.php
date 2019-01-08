<?php

namespace App\Security;

use App\Entity\DriverPresence;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * DriverPresenceVoter class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverPresenceVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const DRIVER_PRESENCE_CREATE = 'driverPresenceCreate';

    public const DRIVER_PRESENCE_DELETE = 'driverPresenceDelete';

    public const DRIVER_PRESENCE_DISPLAY = 'driverPresenceDisplay';

    public const DRIVER_PRESENCE_LIST = 'driverPresenceList';

    public const DRIVER_PRESENCE_MODIFY = 'driverPresenceModify';

    private const ATTRIBUTES = array(
        self::DRIVER_PRESENCE_CREATE,
        self::DRIVER_PRESENCE_DELETE,
        self::DRIVER_PRESENCE_DISPLAY,
        self::DRIVER_PRESENCE_LIST,
        self::DRIVER_PRESENCE_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof DriverPresence && in_array($attribute, self::ATTRIBUTES);
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
            case self::DRIVER_PRESENCE_CREATE:
                return $this->canCreate();
                break;
            case self::DRIVER_PRESENCE_DELETE:
                return $this->canDelete();
                break;
            case self::DRIVER_PRESENCE_DISPLAY:
                return $this->canDisplay();
                break;
            case self::DRIVER_PRESENCE_LIST:
                return $this->canList();
                break;
            case self::DRIVER_PRESENCE_MODIFY:
                return $this->canModify();
                break;
        }

        throw new LogicException('Invalid attribute: ' . $attribute);
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
