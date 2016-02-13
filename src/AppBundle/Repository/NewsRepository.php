<?php

namespace AppBundle\Repository;

/**
 * NewsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NewsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllUrlsBySite($site)
    {
        if(empty($site)){
            return null;
        }

        return $this->getEntityManager()
            ->createQuery(
                'SELECT n.url FROM AppBundle:News n WHERE n.site = :site'
            )
            ->setParameter('site', $site)
            ->getResult();
    }

    public function findAllUnprocessed()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT n FROM AppBundle:News n WHERE n.content is null'
            )
            ->getResult();
    }

    public function findForToday()
    {
        $date = new \DateTime("now");

        $news = $this->getEntityManager()
            ->createQuery(
                'SELECT n FROM AppBundle:News n WHERE n.dateShown = :today '
            )
            ->setParameter('today', $date)
            ->getResult();
        if(count($news) == 0){
            $news = $this->getEntityManager()
                ->createQuery(
                    'SELECT n FROM AppBundle:News n WHERE n.dateShown is null'
                )
                ->getResult();
            shuffle($news);
            $news = array_slice($news,0,15);
        }
        return $news;
    }
}
