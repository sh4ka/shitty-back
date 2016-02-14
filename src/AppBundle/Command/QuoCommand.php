<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QuoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('quo:scrape')
            ->setDescription('Scrape quo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = "http://www.quo.es/rss/feed/site";
        $content = file_get_contents($baseUrl);
        $xml = simplexml_load_string($content);
        if ($xml) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $existingNews = $em->getRepository('AppBundle:News')->findAllUrlsBySite('quo');
            // run over content
            foreach ($xml->channel->item as $newsItem) {
                if(array_search($newsItem->link, $existingNews) === false){
                    $newsEntry = new News();
                    $newsEntry->setSite('quo');
                    $newsEntry->setTitle($newsItem->title);
                    $newsEntry->setUrl($newsItem->link);
                    $newsEntry->setDescription(strip_tags($newsItem->description));
                    $newsEntry->setDateAdded(new \DateTime());
                    $em->persist($newsEntry);
                }
            }
            $em->flush();
        }
    }
}
