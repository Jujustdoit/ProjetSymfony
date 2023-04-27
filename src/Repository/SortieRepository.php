<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function filtre($campus, $nomSortie, $dateMin, $dateMax, $organisateur, $inscrit, $pasInscrit, $sortiesPassees, $idOrganisateur): array {

        $qb = $this->createQueryBuilder('s')
            ->addSelect('e')
            ->join('s.etat', 'e');

        if($campus) {
            $qb->where('s.campus = :campus')
                ->setParameter('campus', $campus);
        }

        if ($nomSortie) {
            $qb->andWhere($qb->expr()->like('s.nom',':stringRecherche'))
                ->setParameter('stringRecherche',"%{$nomSortie}%");
        }
        if($dateMin && $dateMax){
            $qb->andWhere($qb->expr()->between('s.dateDebut',':dateMin',':dateMax'))
                ->setParameter('dateMin',$dateMin)
                ->setParameter('dateMax',$dateMax);
        }
        if($organisateur){
            $qb->andWhere($qb->expr()->eq('s.organisateur', ':idOrganisateur'))
                ->setParameter('idOrganisateur',$idOrganisateur);
        }
        if($inscrit == true && $pasInscrit == false){
            $qb->addSelect('p')
                ->join('s.participants', 'p')
                ->andWhere($qb->expr()->isMemberOf(':participant', 's.participants'))
                ->setParameter('participant', $inscrit);
        }
        if($inscrit == false && $pasInscrit == true) {
            $qb->addSelect('p')
                ->leftJoin('s.participants', 'p')
                ->andWhere(':participant NOT MEMBER OF s.participants')
                ->setParameter('participant', $idOrganisateur);
        }
        if($sortiesPassees == true){
            $qb->andWhere('s.etat = :etat')
                ->setParameter('etat',$sortiesPassees);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
