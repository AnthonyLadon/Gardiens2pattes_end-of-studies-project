<?php

namespace App\Repository;

use App\Entity\Prestataires;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


/**
 * @extends ServiceEntityRepository<Prestataires>
 *
 * @method Prestataires|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prestataires|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prestataires[]    findAll()
 * @method Prestataires[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrestatairesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prestataires::class);
    }

    public function save(Prestataires $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Prestataires $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


//    /**
//     * @return Prestataires[] Returns an array of Prestataires objects
//     */


    // recupére les 4 derniers prestataires

    public function findLastPrestataires(): ?array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult()
        ;
    }


    // Formulaire de recherche de prestataire 

    public function PrestSearch($pseudo, $categories, $localite, $codePostal, $commune, $veto, $jardin, $voiture, $gardeDomicile): ?array
    {
        $query = $this->createQueryBuilder('p')
             //liaison des tables 
            ->leftjoin('p.specialisations', 'spe')
            ->leftjoin('p.utilisateur', 'user')
            ->leftjoin('user.adresse', 'adresse')
            ;
             // test si les variables sont nulles, sinon on execute la requete andWHERE
             if($pseudo){
                 $query->andWhere('user.pseudo LIKE :pseudo')
                 ->setParameter('pseudo', '%'.$pseudo.'%');
             }
             if ($categories) {
                $query->andWhere('spe.nom IN (:categories)')
                    ->setParameter('categories', $categories);
            }
             if($localite){
                 $query->andWhere('adresse.localite LIKE :loc')
                 ->setParameter('loc' , '%'.$localite.'%');
             }
             if($commune){
                 $query->andWhere('adresse.commune LIKE :com')
                 ->setParameter('com', '%'.$commune.'%');
             }
             if($codePostal){
                 $query->andWhere('adresse.codePostal = :cp')
                 ->setParameter('cp', $codePostal);
             }
            if($veto != null){
                $query->andWhere('p.soins_veto = 1');
            }
            if($jardin != null){
                $query->andWhere('p.jardin = 1');
            }
            if($voiture != null){
                $query->andWhere('p.vehicule = 1');
            }
            if($gardeDomicile != null){
                $query->andWhere('p.gardeDomicile = 1');
            }
  
        
             $query->orderBy('user.pseudo');
             $query = $query->getQuery();
             return $query->getResult();
        ;
    }


//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Prestataires
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
