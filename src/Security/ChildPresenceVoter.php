<?php

namespace App\Security;

use App\Entity\ChildPresence;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * ChildPresenceVoter class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ChildPresenceVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const CHILD_PRESENCE_CREATE = 'childPresenceCreate';

    public const CHILD_PRESENCE_DELETE = 'childPresenceDelete';

    public const CHILD_PRESENCE_DISPLAY = 'childPresenceDisplay';

    public const CHILD_PRESENCE_LIST = 'childPresenceList';

    public const CHILD_PRESENCE_MODIFY = 'childPresenceModify';

    private const ATTRIBUTES = array(
        self::CHILD_PRESENCE_CREATE,
        self::CHILD_PRESENCE_DELETE,
        self::CHILD_PRESENCE_DISPLAY,
        self::CHILD_PRESENCE_LIST,
        self::CHILD_PRESENCE_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof ChildPresence && in_array($attribute, self::ATTRIBUTES);
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
            case self::CHILD_PRESENCE_CREATE:
                return $this->canCreate();
                break;
            case self::CHILD_PRESENCE_DELETE:
                return $this->canDelete($token, $subject);
                break;
            case self::CHILD_PRESENCE_DISPLAY:
                return $this->canDisplay($token, $subject);
                break;
            case self::CHILD_PRESENCE_LIST:
                return $this->canList();
                break;
            case self::CHILD_PRESENCE_MODIFY:
                return $this->canModify($token, $subject);
                break;
        }

        throw new LogicException('Invalid attribute: ' . $attribute);
    }

    /**
     * Checks if is allowed to create
     */
    private function canCreate()
    {
        return $this->security->isGranted('ROLE_USER');
    }

    /**
     * Checks if is allowed to delete
     */
    private function canDelete($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
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
     * Checks if is allowed to display
     */
    private function canDisplay($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
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
     * Checks if is allowed to list
     */
    private function canList()
    {
        //Checks roles allowed
        $roles = array(
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
    private function canModify($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
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
     * Checks if registration is linked to the user
     */
    public function isLinked($token, $subject)
    {
        if (null !== $token->getUser()->getUserPersonLink() && null !== $subject->getPerson()->getPersonId()) {
            return($token->getUser()->getUserPersonLink()->getPerson()->getPersonId() === $subject->getPerson()->getPersonId());
        }

        return false;
    }
}
