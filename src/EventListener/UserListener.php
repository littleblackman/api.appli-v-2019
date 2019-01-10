<?php

namespace App\EventListener;

use App\Service\MainServiceInterface;
use App\Service\PersonServiceInterface;
use c975L\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserListener implements EventSubscriberInterface
{
    private $personService;

    public function __construct(
        MainServiceInterface $mainService,
        PersonServiceInterface $personService
    )
    {
        $this->mainService = $mainService;
        $this->personService = $personService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            UserEvent::API_USER_DELETE => 'userApiDelete',
        );
    }

    /**
     * Deletes User and related entities
     */
    public function userApiDelete($event)
    {
        $user = $event->getUser();
        if ($user instanceof \Symfony\Component\Security\Core\User\AdvancedUserInterface) {
            $personLink = $user->getUserPersonLink();
            if (null !== $personLink) {
                //Marks the related Person + Entities as deleted BUT deletes the links
                $this->personService->delete($personLink->getPerson());

                //Marks the User as deleted
                $user
                    ->setUserPersonLink(null)
                    ->setEnabled(false)
                    ->setAllowUse(false)
                ;
                $this->mainService->delete($user);
                $this->mainService->persist($user);
            }
        }

        $event->stopPropagation();
    }
}
