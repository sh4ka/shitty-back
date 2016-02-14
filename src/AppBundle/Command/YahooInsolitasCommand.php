<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YahooInsolitasCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('yahoo:insolitas:scrape')
            ->setDescription('Scrape yahooo insolitas');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = "https://es.noticias.yahoo.com/rss/insolitas";
        $content = file_get_contents($baseUrl);
        $xml = simplexml_load_string($content);
        if ($xml) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $existingNews = $em->getRepository('AppBundle:News')->findAllUrlsBySite('yahoo-insolitas');
            // run over content
            foreach ($xml->channel->item as $newsItem) {
                if(array_search($newsItem->link, $existingNews) === false){
                    $newsEntry = new News();
                    $newsEntry->setSite('yahoo-insolitas');
                    $newsEntry->setTitle($newsItem->title);
                    $newsEntry->setUrl($newsItem->link);
                    $description = strip_tags($newsItem->description);
                    $newsEntry->setDescription($description);
                    $newsEntry->setDateAdded(new \DateTime());
                    $em->persist($newsEntry);
                }
            }
            $em->flush();
        }
    }
}
