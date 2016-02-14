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
                'SELECT n FROM AppBundle:News n WHERE n.content is null and n.leadImageUrl is NULL'
            )
            ->getResult();
    }

    public function findForToday()
    {
        $date = new \DateTime("now");

        $news = $this->getEntityManager()
            ->createQuery(
                'SELECT n FROM AppBundle:News n WHERE n.dateShown = :today AND n.leadImageUrl is not NULL '
            )
            ->setParameter('today', $date->format('Y-m-d'))
            ->setMaxResults(16)
            ->getResult();
        $gotNews = count($news);
        if(count($news) < 16){
            $freshNews = $this->getEntityManager()
                ->createQuery(
                    'SELECT n FROM AppBundle:News n WHERE n.dateShown is null AND n.leadImageUrl is not NULL'
                );
            if(!empty($freshNews)){
                $freshNews = $freshNews->getResult();
                shuffle($freshNews);
                $freshNews = array_slice($freshNews, 0, 16-$gotNews);
                $news = array_merge($news, $freshNews);
            }
        }
        shuffle($news);
        return $news;
    }
}
