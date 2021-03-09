<?php

namespace App\Repository;

use App\Entity\Meta;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Meta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meta[]    findAll()
 * @method Meta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meta::class);
    }

    public function getSingles($findGender = 'V', $provId)
    {
        $query =
            $this->createQueryBuilder('meta')
            ->addselect('user', 'pics')
            ->Join('meta.user', 'user')
            ->Join('user.pictures', 'pics')
            ->where('pics.mainfoto = 1');

            if(!empty($findGender)) {
                $query->andWhere('user.male_female = :sexe ');
                $query->setParameter('sexe', substr($findGender, 0, 1));
            }
            if(!empty($provId)) {
                $query->andWhere('meta.provincie = :provId ');
                $query->setParameter('provId', $provId);
            }

            return $query->getQuery()->execute();
    }

    public function getFavos()
    {
        $query =
            $this->createQueryBuilder('meta')
                ->addselect('user', 'pics', 'favorites')
                ->Join('meta.user', 'user')
                ->Join('user.pictures', 'pics')
                ->Join('user.favorites', 'favorites')
                //->where("favorites.sender = :sender")
                ->where("user.id = :sender")
                //->setParameter('sender', 8)
                ->setParameter('sender', 8)
                ->andWhere('pics.mainfoto = 1');

        return $query->getQuery()->execute();
    }

    public function getSingles2()
    {
        $em = $this->getEntityManager();
        $q  = $em->createQuery("

                SELECT m,u,pics
                FROM Meta::class m

                INNER JOIN m.user u         

                LEFT JOIN u.pictures pics
                WHERE  pics.mainfoto = 1

        ");
           // ->setParameter('state_culture', $culture)
          //  ->setParameter('country_culture', $culture);

        return $q->getResult();
    }

    /**
     * @return Metas[]
     */
    public function findBySearchQuery(int $provId, $findGender = 'V', int $limit = Meta::NUM_ITEMS): array
    {
        $query =
            $this->createQueryBuilder('meta')
                ->addselect('user', 'pics')
                ->Join('meta.user', 'user')
                ->Join('user.pictures', 'pics')
                ->where('pics.mainfoto = 1');

        if(!empty($findGender)) {
            $query->andWhere('user.male_female = :sexe ');
            $query->andWhere('meta.provincie = :provId ');
            $query->setParameter('sexe', substr($findGender, 0, 1));
            $query->setParameter('provId', $provId);
        }

        return $query
            ->orderBy('meta.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatest(int $page = 1, Tag $tag = null): Pagerfanta
    {
        $qb = $this->createQueryBuilder('m')
            ->addSelect('a', 't')
            //->innerJoin('p.author', 'a')
            //->leftJoin('p.tags', 't')
            ->where('p.publishedAt <= :now')
            ->orderBy('p.publishedAt', 'DESC')
            ->setParameter('now', new \DateTime());

        if (null !== $tag) {
            $qb->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        return $this->createPaginator($qb->getQuery(), $page);
    }

    private function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(Post::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
