<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Address;

class AddressVoter extends Voter
{
    public const ADDRESS_DELETE = 'addressDelete';
    public const ADDRESS_DISPLAY = 'addressDisplay';
    public const ADDRESS_LIST = 'addressList';
    public const ADDRESS_MODIFY = 'addressModify';

    private const ATTRIBUTES = array(
        self::ADDRESS_DELETE,
        self::ADDRESS_DISPLAY,
        self::ADDRESS_LIST,
        self::ADDRESS_MODIFY,
    );

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Address && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //Defines access rights
        switch ($attribute) {
            case self::ADDRESS_DELETE:
            case self::ADDRESS_DISPLAY:
            case self::ADDRESS_LIST:
            case self::ADDRESS_MODIFY:
//TODO basé sur same origin
return true;
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}
