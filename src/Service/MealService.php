<?php

namespace App\Service;

use App\Entity\Food;
use App\Entity\Meal;
use App\Entity\Child;
use App\Entity\MealFoodLink;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
     * Adds link between Meal and Food
     */
    public function addLink(int $foodId, Meal $object)
    {
        $food = $this->em->getRepository('App:Food')->findOneById($foodId);
        if ($food instanceof Food && !$food->getSuppressed()) {
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
    public function create(string $data)
    {
        //Submits data
        $object = new Meal();
        $this->mainService->create($object);
        $data = $this->mainService->submit($object, 'meal-create', $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Adds links from food to meal
        if (array_key_exists('links', $data)) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                foreach ($links as $link) {
                    $this->addLink((int) $link['foodId'], $object);
                }
            }
        }

        //Persists data
        $this->mainService->persist($object);

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
        //Removes links from meal to food
        if (!$object->getFoods()->isEmpty()) {
            foreach ($object->getFoods() as $link) {
                $this->em->remove($link);
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

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
     * Returns the list of all meals by date
     * @return array
     */
    public function findByChildAndDate($childId, $date)
    {

        $child = $this->em->getRepository('App:Child')->find($childId);

        return $this->em
            ->getRepository('App:Meal')
            ->findByChildAndDate($child, $date)
        ;

    }


    /**
     * Returns the latest meal by child
     * @return array
     */
    public function latestMealByChild($childId)
    {
      $child = $this->em->getRepository('App:Child')->find($childId);

      return $this->em
          ->getRepository('App:Meal')
          ->findLatestByChild($child)
      ;

    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Meal $object)
    {
        if (null === $object->getDate() ||
            (null === $object->getChild() && null === $object->getPerson() && null === $object->getFreeName())) {
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

        //Modifies links from food to meal
        if (array_key_exists('links', $data)) {
            $links = $data['links'];

            if (null !== $links && is_array($links) && !empty($links)) {
                //Removes existing links
                foreach ($object->getFoods() as $food) {
                    $this->em->remove($food);
                }

                foreach ($links as $link) {
                    $this->addLink((int) $link['foodId'], $object);
                }
            }
        }

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
        if (null !== $object->getChild() && !$object->getChild()->getSuppressed()) {
            $objectArray['child'] = $this->mainService->toArray($object->getChild()->toArray());
        }

        //Gets related person
        if (null !== $object->getPerson() && !$object->getPerson()->getSuppressed()) {
            $objectArray['person'] = $this->mainService->toArray($object->getPerson()->toArray());
        }

        //Gets related foods
        if (null !== $object->getFoods()) {
            $foods = array();
            foreach($object->getFoods() as $foodLink) {
                if (!$foodLink->getFood()->getSuppressed()) {
                    $foods[] = $this->mainService->toArray($foodLink->getFood()->toArray());
                }
            }
            $objectArray['foods'] = $foods;
        }

        return $objectArray;
    }

    /**
     * Returns the total of meal for a date
     */
    public function totalMealByDate($date)
    {
        $meals = $this->em
            ->getRepository('App:Meal')
            ->findAllByDate($date);
        ;

        $mealsArray = array(
            'meals' => 0,
            'child' => 0,
            'person' => 0,
            'freeName' => 0,
            'food' => array(
                'child' => array(),
                'person' => array(),
                'freeName' => array(),
            ),
        );

        //Defines meal totals
        foreach ($meals as $meal) {
            $mealsArray['meals'] += 1;
            $mealsArray['child'] += null !== $meal->getChild() ? 1 : 0;
            $mealsArray['person'] += null !== $meal->getPerson() ? 1 : 0;
            $mealsArray['freeName'] += null !== $meal->getFreeName() ? 1 : 0;

            //Defines food totals
            foreach ($meal->getFoods() as $food) {
                $foodId = $food->getFood()->getFoodId();
                $mealsArray['food'][$foodId] = isset($mealsArray['food'][$foodId]) ? $mealsArray['food'][$foodId] + 1 : 1;

                //Child
                if (null !== $meal->getChild()) {
                    $mealsArray['food']['child'][$foodId] = isset($mealsArray['food']['child'][$foodId]) ? $mealsArray['food']['child'][$foodId] + 1 : 1;
                //Person
                } elseif (null !== $meal->getPerson()) {
                    $mealsArray['food']['person'][$foodId] = isset($mealsArray['food']['person'][$foodId]) ? $mealsArray['food']['person'][$foodId] + 1 : 1;
                //FreeName
                } elseif (null !== $meal->getFreeName()) {
                    $mealsArray['food']['freeName'][$foodId] = isset($mealsArray['food']['freeName'][$foodId]) ? $mealsArray['food']['freeName'][$foodId] + 1 : 1;
                }
            }
        };

        return $mealsArray;
    }
}
