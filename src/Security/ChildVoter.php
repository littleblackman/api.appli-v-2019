<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Child;

class ChildVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const CHILD_CREATE = 'childCreate';
    public const CHILD_DELETE = 'childDelete';
    public const CHILD_DISPLAY = 'childDisplay';
    public const CHILD_LIST = 'childList';
    public const CHILD_MODIFY = 'childModify';

    private const ATTRIBUTES = array(
        self::CHILD_CREATE,
        self::CHILD_DELETE,
        self::CHILD_DISPLAY,
        self::CHILD_LIST,
        self::CHILD_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Child && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::CHILD_CREATE:
                return $this->security->isGranted('ROLE_USER');
                break;
            case self::CHILD_DISPLAY:
                return $this->isAllowedDisplay($token, $subject);
                break;
            case self::CHILD_DELETE:
            case self::CHILD_MODIFY:
                return $this->isAllowedModify($token, $subject);
                break;
            case self::CHILD_LIST:
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }

    /**
     * Checks if is allowed to display
     */
    private function isAllowedDisplay($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
            'ROLE_ADMIN',
            'ROLE_BACKOFFICE',
            'ROLE_COACH',
            'ROLE_DRIVER',
        );

        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }

        return $this->isLinked($token, $subject);
    }

    /**
     * Checks if is allowed to modify/delete
     */
    private function isAllowedModify($token, $subject)
    {
        //Checks roles allowed
        $roles = array(
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
        $personId = $token->getUser()->getUserPersonLink()->getPerson()->getPersonId();
        foreach ($subject->getPersons() as $person) {
            if ($person->getPerson()->getPersonId() === $personId) {
                return true;
            }
        }

        return false;
    }
}
