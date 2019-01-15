<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Component;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\ProductCategoryLink;
use App\Entity\ProductComponentLink;
use App\Entity\ProductDateLink;
use App\Entity\ProductHourLink;
use App\Entity\ProductLocationLink;
use App\Entity\ProductSportLink;
use App\Entity\Sport;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * ProductService class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ProductService implements ProductServiceInterface
{
    private $componentService;

    private $em;

    private $mainService;

    public function __construct(
        ComponentServiceInterface $componentService,
        EntityManagerInterface $em,
        MainServiceInterface $mainService
    )
    {
        $this->componentService = $componentService;
        $this->em = $em;
        $this->mainService = $mainService;
    }

    /**
     * Adds link between Product and Component
     */
    public function addComponentLink(int $componentId, Product $object)
    {
        $component = $this->em->getRepository('App:Component')->findOneById($componentId);
        if ($component instanceof Component && !$component->getSuppressed()) {
            $productComponentLink = new ProductComponentLink();
            $productComponentLink
                ->setProduct($object)
                ->setComponent($component)
            ;
            $this->em->persist($productComponentLink);
        }
    }

    /**
     * Adds link between Product and Sport
     */
    public function addSportLink(int $sportId, Product $object)
    {
        $sport = $this->em->getRepository('App:Sport')->findOneById($sportId);
        if ($sport instanceof Sport && !$sport->getSuppressed()) {
            $productSportLink = new ProductSportLink();
            $productSportLink
                ->setProduct($object)
                ->setSport($sport)
            ;
            $this->em->persist($productSportLink);
        }
    }

    /**
     * Adds link between Product and Category
     */
    public function addCategoryLink(int $categoryId, Product $object)
    {
        $category = $this->em->getRepository('App:Category')->findOneById($categoryId);
        if ($category instanceof Category && !$category->getSuppressed()) {
            $productCategoryLink = new ProductCategoryLink();
            $productCategoryLink
                ->setProduct($object)
                ->setCategory($category)
            ;
            $this->em->persist($productCategoryLink);
        }
    }

    /**
     * Adds link between Product and Location
     */
    public function addLocationLink(int $locationId, Product $object)
    {
        $location = $this->em->getRepository('App:Location')->findOneById($locationId);
        if ($location instanceof Location && !$location->getSuppressed()) {
            $productLocationLink = new ProductLocationLink();
            $productLocationLink
                ->setProduct($object)
                ->setLocation($location)
            ;
            $this->em->persist($productLocationLink);
        }
    }

    /**
     * Adds link between Product and Date
     */
    public function addDateLink($date, Product $object)
    {
        $date = $date instanceof DateTime ? $date : new DateTime($date);
        $productDateLink = new ProductDateLink();
        $productDateLink
            ->setProduct($object)
            ->setDate($date)
        ;
        $this->em->persist($productDateLink);
    }

    /**
     * Adds link between Product and Hour
     */
    public function addHourLink($start, $end, Product $object)
    {
        $start = $start instanceof DateTime ? $start : new DateTime('1970-01-01' . $start);
        $end = $end instanceof DateTime ? $end : new DateTime('1970-01-01' . $end);
        $productHourLink = new ProductHourLink();
        $productHourLink
            ->setProduct($object)
            ->setStart($start)
            ->setEnd($end)
        ;
        $this->em->persist($productHourLink);
    }

    /**
     * Adds specific data that could not be added via generic method
     */
    public function addSpecificData(Product $object, array $data)
    {
        //Should be done from RideType but it returns null...
        if (isset($data['hourDropin'])) {
            $object->setHourDropin(DateTime::createFromFormat('H:i:s', $data['hourDropin']));
        }
        if (isset($data['hourDropoff'])) {
            $object->setHourDropoff(DateTime::createFromFormat('H:i:s', $data['hourDropoff']));
        }

        //Converts to boolean
        if (isset($data['transport'])) {
            $object->setTransport((bool) $data['transport']);
        }
        if (isset($data['isLocationSelectable'])) {
            $object->setIsLocationSelectable((bool) $data['isLocationSelectable']);
        }
        if (isset($data['isDateSelectable'])) {
            $object->setIsDateSelectable((bool) $data['isDateSelectable']);
        }
        if (isset($data['isHourSelectable'])) {
            $object->setIsHourSelectable((bool) $data['isHourSelectable']);
        }
        if (isset($data['isSportAssociated'])) {
            $object->setIsSportAssociated((bool) $data['isSportAssociated']);
        }

        //Adds/Removes links to products
        $linksArray = array(
            'categories' => 'category',
            'components' => 'component',
            'locations' => 'location',
            'sports' => 'sport',
        );
        foreach ($linksArray as $key => $value) {
            if (array_key_exists($key, $data)) {
                $links = $data[$key];
                //Adds link
                if (null !== $links && is_array($links) && !empty($links)) {
                    $method = 'add' . ucfirst($value) . 'Link';
                    foreach ($links as $link) {
                        $this->$method((int) $link[$value], $object);
                    }
                //Removes link
                } elseif (null === $data[$key]) {
                    $method = 'get' . ucfirst($key);
                    foreach ($object->$method() as $productLink) {
                        $this->em->remove($productLink);
                    }
                }
            }
        }

        //Adds links to dates
        if (isset($data['dates'])) {
            $dates = $data['dates'];
            if (null !== $dates && is_array($dates) && !empty($dates)) {
                foreach ($dates as $date) {
                    $this->addDateLink($date['date'], $object);
                }
            }
        }

        //Adds links to hours
        if (isset($data['hours'])) {
            $hours = $data['hours'];
            if (null !== $hours && is_array($hours) && !empty($hours)) {
                foreach ($hours as $hour) {
                    $this->addHourLink($hour['start'], $hour['end'], $object);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $data)
    {
        //Submits data
        $object = new Product();
        $data = $this->mainService->submit($object, 'product-create', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->create($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Produit ajouté',
            'product' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Product $object)
    {
        //Removes links to products
        $linksArray = array(
            'categories',
            'components',
            'dates',
            'hours',
            'locations',
            'sports',
        );
        foreach ($linksArray as $linkArray) {
            $method = 'get' . ucfirst($linkArray);
            $links = $object->$method();
            if (null !== $links && !empty($links)) {
                foreach ($links as $link) {
                    $this->em->remove($link);
                }
            }
        }

        //Persists data
        $this->mainService->delete($object);
        $this->mainService->persist($object);

        return array(
            'status' => true,
            'message' => 'Produit supprimé',
        );
    }

    /**
     * Returns the list of all products in the array format
     * @return array
     */
    public function findAll()
    {
        return $this->em
            ->getRepository('App:Product')
            ->findAll()
        ;
    }

    /**
     * Searches the term in the Product collection
     * @return array
     */
    public function findAllSearch(string $term)
    {
        return $this->em
            ->getRepository('App:Product')
            ->findAllSearch($term)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntityFilled(Product $object)
    {
        if (null === $object->getNameFr() ||
            null === $object->getDescriptionFr()) {
            throw new UnprocessableEntityHttpException('Missing data for Product -> ' . json_encode($object->toArray()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Product $object, string $data)
    {
        //Submits data
        $data = $this->mainService->submit($object, 'product-modify', $data);
        $this->addSpecificData($object, $data);

        //Checks if entity has been filled
        $this->isEntityFilled($object);

        //Persists data
        $this->mainService->modify($object);
        $this->mainService->persist($object);

        //Returns data
        return array(
            'status' => true,
            'message' => 'Produit modifié',
            'product' => $this->toArray($object),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(Product $object)
    {
        //Main data
        $objectArray = $this->mainService->toArray($object->toArray());

        //Gets related family
        if (null !== $object->getFamily() && !$object->getFamily()->getSuppressed()) {
            $objectArray['family'] = $this->mainService->toArray($object->getFamily()->toArray());
        }

        //Gets related season
        if (null !== $object->getSeason() && !$object->getSeason()->getSuppressed()) {
            $objectArray['season'] = $this->mainService->toArray($object->getSeason()->toArray());
        }

        //Gets related links
        $linksArray = array(
            'categories' => 'category',
            'components' => 'component',
            'locations' => 'location',
            'sports' => 'sport',
        );
        foreach ($linksArray as $key => $value) {
            $methodCollection = 'get' . ucfirst($key);
            $methodObject = 'get' . ucfirst($value);
            if (null !== $object->$methodCollection()) {
                $links = array();
                foreach($object->$methodCollection() as $link) {
                    if (!$link->$methodObject()->getSuppressed()) {
                        $links[] = $this->mainService->toArray($link->$methodObject()->toArray());
                    }
                }
                $objectArray[$key] = $links;
            }
        }

        //Gets related dates
        if (null !== $object->getDates()) {
            $dates = array();
            foreach($object->getDates() as $date) {
                if (null !== $date->getDate()) {
                    $dates[] = $date->getDate()->format('Y-m-d');
                }
            }
            $objectArray['dates'] = $dates;
        }

        //Gets related hours
        if (null !== $object->getHours()) {
            $hours = array();
            foreach($object->getHours() as $hour) {
                if (null !== $hour->getStart()) {
                    $hours[]['start'] = $hour->getStart()->format('H:i');
                }
                if (null !== $hour->getEnd()) {
                    $hours[]['end'] = $hour->getEnd()->format('H:i');
                }
            }
            $objectArray['hours'] = $hours;
        }

        return $objectArray;
    }
}
