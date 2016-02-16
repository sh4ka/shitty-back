<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class XatakaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('xataka:scrape')
            ->setDescription('Scrape xataka');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = "http://feeds.weblogssl.com/xataka2";
        $xml = simplexml_load_file($baseUrl);
        if ($xml) {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $existingNews = $em->getRepository('AppBundle:News')->findAllUrlsBySite('xataka');
            // run over content
            foreach ($xml->channel->item as $newsItem) {
                if(array_search(['url' => $newsItem->link], $existingNews) === false){
                    $newsEntry = new News();
                    $newsEntry->setSite('xataka');
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
