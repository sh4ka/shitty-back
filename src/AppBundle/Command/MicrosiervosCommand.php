<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MicrosiervosCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('microsiervos:scrape')
            ->setDescription('Scrape microsiervos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = "http://www.microsiervos.com/index.xml";
        $xml = simplexml_load_file($baseUrl);
        if ($xml) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $existingNews = $em->getRepository('AppBundle:News')->findAllUrlsBySite('microsiervos');
            // run over content
            foreach ($xml->channel->item as $newsItem) {
                if(array_search(['url' => $newsItem->link], $existingNews) === false){
                    $newsEntry = new News();
                    $newsEntry->setSite('microsiervos');
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
