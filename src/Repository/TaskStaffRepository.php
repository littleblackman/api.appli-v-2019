<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Staff;

/**
 * TaskStaffRepository class
 * @author Sandy Razafirimo <sandyrazafitrimo@gmail.com>
 */
class TaskStaffRepository extends EntityRepository
{
  /**
   * Returns all the tasks for a staff at a date
   */
  public function findByStaffAndDate(Staff $staff, $date)
  {
      return $this->createQueryBuilder('s')
          ->where('s.dateTask LIKE :date')
          ->andWhere('s.staff = :staff')
          ->orderBy('s.dateTask', 'ASC')
          ->setParameter('date', '%' . $date . '%')
          ->setParameter('staff', $staff)
          ->getQuery()
          ->getResult()
      ;
  }


  /**
   * Returns the one task done by staff and date (first or last)
   */
  public function findOneByStaffAndDate(Staff $staff, $date, $which)
  {
      $qb = $this->createQueryBuilder('s')
          ->where('s.dateTask LIKE :date')
          ->andWhere('s.staff = :staff')
          ->andWhere("s.step = 'DONE' ");

      if($which == "first") $qb->orderBy('s.dateTask', 'ASC');
      if($which == "last") $qb->orderBy('s.dateTask', 'DESC');

      $qb->setParameter('date', '%' . $date . '%')
          ->setParameter('staff', $staff)
          ->setMaxResults(1);


      return $qb
          ->getQuery()
          ->getOneOrNullResult();
  }


  /**
   * Returns all the tasks by date
   */
  public function findByDate($date)
  {
      return $this->createQueryBuilder('s')
          ->where('s.dateTask LIKE :date')
          ->orderBy('s.staff', 'ASC')
          //orderBy('s.dateTask', 'ASC')
          ->setParameter('date', '%' . $date . '%')
          ->getQuery()
          ->getResult()
      ;
  }


  /**
   * Returns all the tasks by step
   */
  public function findByStep($step, $staff = null)
  {
      $qb = $this->createQueryBuilder('s')
          ->where('s.step LIKE :step')
          ->orderBy('s.dateTask', 'ASC')
          ->setParameter('step', $step)
      ;

      if($staff) {
        $qb->andWhere('s.staff = :staff')
          ->setParameter('staff', $staff);
      }

      return $qb
        ->getQuery()
        ->getResult();
  }


}
