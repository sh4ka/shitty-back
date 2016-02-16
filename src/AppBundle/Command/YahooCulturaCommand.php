<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class YahooCulturaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('yahoo:cultura:scrape')
            ->setDescription('Scrape yahooo cultura');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = "https://es.noticias.yahoo.com/rss/cultura";
        $xml = simplexml_load_file($baseUrl);
        if ($xml) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $existingNews = $em->getRepository('AppBundle:News')->findAllUrlsBySite('yahoo');
            // run over content
            foreach ($xml->channel->item as $newsItem) {
                if(array_search(['url' => $newsItem->link], $existingNews) === false){
                    $newsEntry = new News();
                    $newsEntry->setSite('yahoo');
                    $newsEntry->setTitle($newsItem->title);
                    $newsEntry->setUrl($newsItem->link);
                    $newsEntry->setDateAdded(new \DateTime());
                    $em->persist($newsEntry);
                }
            }
            $em->flush();
        }
    }
}
