<?php

namespace App\Security;

use App\Entity\Driver;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * DriverVoter class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class DriverVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const DRIVER_CREATE = 'driverCreate';

    public const DRIVER_DELETE = 'driverDelete';

    public const DRIVER_DISPLAY = 'driverDisplay';

    public const DRIVER_LIST = 'driverList';

    public const DRIVER_MODIFY = 'driverModify';

    private const ATTRIBUTES = array(
        self::DRIVER_CREATE,
        self::DRIVER_DELETE,
        self::DRIVER_DISPLAY,
        self::DRIVER_LIST,
        self::DRIVER_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Driver && in_array($attribute, self::ATTRIBUTES);
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
            case self::DRIVER_CREATE:
                return $this->canCreate($token, $subject);
                break;
            case self::DRIVER_DELETE:
                return $this->canDelete($token, $subject);
                break;
            case self::DRIVER_DISPLAY:
                return $this->canDisplay($token, $subject);
                break;
            case self::DRIVER_LIST:
                return $this->canList($token, $subject);
                break;
            case self::DRIVER_MODIFY:
                return $this->canModify($token, $subject);
                break;
        }

        throw new LogicException('Invalid attribute: ' . $attribute);
    }

    /**
     * Checks if is allowed to create
     */
    private function canCreate($token, $subject)
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
    private function canDelete($token, $subject)
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
    private function canDisplay($token, $subject)
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
    private function canList($token, $subject)
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

        return false;
    }
}
