<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Season;

/**
 * SeasonVoter class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class SeasonVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const SEASON_CREATE = 'seasonCreate';
    public const SEASON_DELETE = 'seasonDelete';
    public const SEASON_DISPLAY = 'seasonDisplay';
    public const SEASON_LIST = 'seasonList';
    public const SEASON_MODIFY = 'seasonModify';

    private const ATTRIBUTES = array(
        self::SEASON_CREATE,
        self::SEASON_DELETE,
        self::SEASON_DISPLAY,
        self::SEASON_LIST,
        self::SEASON_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Season && in_array($attribute, self::ATTRIBUTES);
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
            case self::SEASON_CREATE:
                return $this->canCreate($token, $subject);
                break;
            case self::SEASON_DELETE:
                return $this->canDelete($token, $subject);
                break;
            case self::SEASON_DISPLAY:
                return $this->canDisplay($token, $subject);
                break;
            case self::SEASON_LIST:
                return $this->canList($token, $subject);
                break;
            case self::SEASON_MODIFY:
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
