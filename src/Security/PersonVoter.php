<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Person;

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
    public const PERSON_SEARCH = 'personSearch';

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
            return $subject instanceof Person && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
#Supprimer lorsque la gestion des droits sera ok (24/10/2018)
return true;
        //Defines access rights
        switch ($attribute) {
            case self::PERSON_CREATE:
                return $this->security->isGranted('ROLE_USER');
                break;
            case self::PERSON_DISPLAY:
            case self::PERSON_LIST:
            case self::PERSON_SEARCH:
                return $this->isAllowedDisplay($token, $subject);
                break;
            case self::PERSON_DELETE:
            case self::PERSON_MODIFY:
                return $this->isAllowedModify($token, $subject);
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
        if (null !== $token->getUser()->getUserPersonLink()) {
            $personId = $token->getUser()->getUserPersonLink()->getPerson()->getPersonId();
            if ($subject->getPersonId() === $personId) {
                return true;
            }

            return false;
        }
    }
}
