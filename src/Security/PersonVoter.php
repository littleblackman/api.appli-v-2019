<?php

namespace App\Security;

use App\Entity\Person;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * PersonVoter class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class PersonVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const PERSON_CREATE = 'personCreate';

    public const PERSON_DELETE = 'personDelete';

    public const PERSON_DISPLAY = 'personDisplay';

    public const PERSON_LIST = 'personList';

    public const PERSON_MODIFY = 'personModify';

    private const ATTRIBUTES = array(
        self::PERSON_CREATE,
        self::PERSON_DELETE,
        self::PERSON_DISPLAY,
        self::PERSON_LIST,
        self::PERSON_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Person && in_array($attribute, self::ATTRIBUTES);
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
            'ROLE_TRAINEE',
            'ROLE_COACH',
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
     * Checks if is allowed to list
     */
    private function canList($token, $subject)
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
            if ($subject->getPersonId() === $personId) {
                return true;
            }

            return false;
        }
    }
}
