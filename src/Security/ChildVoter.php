<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use App\Entity\Child;

class ChildVoter extends Voter
{
    /**
     * Stores AccessDecisionManagerInterface
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

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

    public function __construct(
        AccessDecisionManagerInterface $decisionManager
    )
    {
        $this->decisionManager = $decisionManager;
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
                return $this->decisionManager->decide($token, array('ROLE_USER'));
                break;
            case self::CHILD_DISPLAY:
                return $this->isAllowedDisplay($token);
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

    private function isAdmin($token)
    {
        return $this->decisionManager->decide($token, array('ROLE_ADMIN'));
    }

    private function isAllowedDisplay($token)
    {
        return $this->decisionManager->decide($token, array('ROLE_ADMIN', 'ROLE_BACKOFFICE', 'ROLE_COACH', 'ROLE_DRIVER'));
    }

    private function isAllowedModify($token, $subject)
    {
        return $this->isAdmin($token);
    }
}
