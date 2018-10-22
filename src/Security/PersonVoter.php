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
        //Defines access rights
        switch ($attribute) {
            case self::PERSON_CREATE:
                return $this->security->isGranted('ROLE_USER');
                break;
            case self::PERSON_DELETE:
            case self::PERSON_DISPLAY:
            case self::PERSON_MODIFY:
            case self::PERSON_LIST:
return true;
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}
