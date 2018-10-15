<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Child;

class ChildVoter extends Voter
{
    public const CHILD_DELETE = 'childDelete';
    public const CHILD_DISPLAY = 'childDisplay';
    public const CHILD_LIST = 'childList';
    public const CHILD_MODIFY = 'childModify';

    private const ATTRIBUTES = array(
        self::CHILD_DELETE,
        self::CHILD_DISPLAY,
        self::CHILD_LIST,
        self::CHILD_MODIFY,
    );

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
            case self::CHILD_DELETE:
            case self::CHILD_DISPLAY:
            case self::CHILD_LIST:
            case self::CHILD_MODIFY:
//TODO basé sur same origin
return true;
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}
