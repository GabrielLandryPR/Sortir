<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByCriterias() {

        $q= $this->createQueryBuilder('s')
            ->orderBy('s.noSite', order: 'ASC');

        return $q->getQuery()->getResult();

    }

    public function findFilteredSorties($organizer = null, $registered = null, $notRegistered = null, $past = false, $site = null, $startDate = null, $endDate = null, $searchName = null) {
        $qb = $this->createQueryBuilder('s');
        $conditions = [];

        if ($organizer) {
            $conditions[] = $qb->expr()->eq('s.idOrga', ':organizer');
            $qb->setParameter('organizer', $organizer);
        }

        if ($registered) {
            $conditions[] = $qb->expr()->isMemberOf(':registeredUser', 's.Users');
            $qb->setParameter('registeredUser', $registered);
        }

        if ($notRegistered) {
            $conditions[] = $qb->expr()->not($qb->expr()->isMemberOf(':notRegisteredUser', 's.Users'));
            $qb->setParameter('notRegisteredUser', $notRegistered);
            $conditions[] = $qb->expr()->neq('s.idOrga', ':notRegisteredUser');
        }

        if ($past) {
            $conditions[] = $qb->expr()->lt('s.dateFin', ':now');
            $qb->setParameter('now', new \DateTime());
        }

        if ($site) {
            $conditions[] = $qb->expr()->eq('s.noSite', ':site');
            $qb->setParameter('site', $site);
        }

        if ($startDate) {
            $conditions[] = $qb->expr()->gte('s.dateDebut', ':startDate');
            $qb->setParameter('startDate', new \DateTime($startDate));
        }

        if ($endDate) {
            $conditions[] = $qb->expr()->lte('s.dateFin', ':endDate');
            $qb->setParameter('endDate', new \DateTime($endDate));
        }

        if ($searchName) {
            $conditions[] = $qb->expr()->like('s.nomSortie', ':searchName');
            $qb->setParameter('searchName', '%' . $searchName . '%');
        }

        if (!empty($conditions)) {
            $qb->andWhere($qb->expr()->andX()->addMultiple($conditions));
        }

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
