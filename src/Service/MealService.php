<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Service\MainServiceInterface;
use App\Entity\Food;
use App\Entity\Meal;
use App\Entity\MealFoodLink;

/**
 * MealService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class MealService implements MealServiceInterface
{
    private $em;
    private $mainService;

    public function __construct(
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * {@inheritdoc}
     */
    public function addLink(int $foodId, Meal $object)
    {
        $food = $this->em->getRepository('App:Food')->findOneById($foodId);
        if ($food instanceof Food) {
            $mealFoodLink = new MealFoodLink();
            $mealFoodLink
                ->setFood($food)
                ->setMeal($object)
            ;
            $this->em->persist($mealFoodLink);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Meal $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'meal-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Adds links from food to meal
        if (isset($data['links'])) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addLink((int) $link['foodId'], $object);
                }

                //Persists in DB
                $this->em->flush();
                $this->em->refresh($object);
            }
        }

        //Returns data
        return array(
            'status' => true,
            'message' => 'Repas ajouté',
            'meal' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Meal $object)
    {
        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        //Removes links from meal to food
        $links = $object->getFoods();
        if (null !== $links && !empty($links)) {
            foreach ($links as $link) {
                $this->em->remove($link);
            }

            //Persists in DB
            $this->em->flush();
            $this->em->refresh($object);
        }

        return array(
            'status' => true,
            'message' => 'Repas supprimé',
        );
    }

    /**
     * Returns the list of all meals by date
     * @return array
     */
    public function findAllByDate(string $date)
    {
        return $this->em
            ->getRepository('App:Meal')
            ->findAllByDate($date)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Meal $object)
    {
        if (null === $object->getDate() ||
            (null === $object->getChild() && null === $object->getPerson())) {
            throw new UnprocessableEntityHttpException('Missing data for Meal -> ' . json_encode($object->toArray()));
        }

        //Suppress Person if both Child and Person are set
        if (null !== $object->getChild() && null !== $object->getPerson()) {
            $object->setPerson(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Meal $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'meal-modify', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Repas modifié',
            'meal' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Meal $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related child
        if (null !== $object->getChild()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related person
        if (null !== $object->getPerson()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related foods
        if (null !== $object->getFoods()) {
            $foods = array();
            $i = 0;
            foreach($object->getFoods() as $foodLink) {
                $foods[$i] = $this->mainService->toArray($foodLink->getFood()->toArray());
                $i++;
            }
            $objectArray['foods'] = $foods;
        }

        return $objectArray;
    }
}
