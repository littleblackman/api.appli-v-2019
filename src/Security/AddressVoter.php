<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Address;

class AddressVoter extends Voter
{
    /**
     * Stores Security
     * @var Security
     */
    private $security;

    public const ADDRESS_CREATE = 'addressCreate';
    public const ADDRESS_DELETE = 'addressDelete';
    public const ADDRESS_DISPLAY = 'addressDisplay';
    public const ADDRESS_LIST = 'addressList';
    public const ADDRESS_MODIFY = 'addressModify';

    private const ATTRIBUTES = array(
        self::ADDRESS_CREATE,
        self::ADDRESS_DELETE,
        self::ADDRESS_DISPLAY,
        self::ADDRESS_LIST,
        self::ADDRESS_MODIFY,
    );

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (null !== $subject) {
            return $subject instanceof Address && in_array($attribute, self::ATTRIBUTES);
        }

        return in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
#Supprimer lorsque la gestion des droits sera ok (24/10/2018)
return true;
        //Defines access rights
        switch ($attribute) {
            case self::ADDRESS_CREATE:
                return $this->security->isGranted('ROLE_USER');
                break;
            case self::ADDRESS_DELETE:
            case self::ADDRESS_DISPLAY:
            case self::ADDRESS_MODIFY:
            case self::ADDRESS_LIST:
return true;
                break;
        }

        throw new \LogicException('Invalid attribute: ' . $attribute);
    }
}
