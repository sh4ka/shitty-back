<?php
/**
 * Created by PhpStorm.
 * User: jesus
 * Date: 18/02/2016
 * Time: 21:44
 */

namespace AppBundle\Command;


use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NewsGetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('news:get')
            ->setDescription('Get news from feeds')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Fetching news from configured sources');

        $em = $this->getContainer()->get('doctrine')->getManager();
        $sources = $em->getRepository('AppBundle:Source')->findBy(['enabled' => true]);

        if(count($sources) == 0){
            $io->warning('You have 0 configured sources');
        } else{
            foreach($sources as $newsSource) {
                $xml = simplexml_load_file($newsSource->getUrl());
                if ($xml) {
                    $existingNews = $em->getRepository('AppBundle:News')->findAllUrlsBySite($newsSource->getName());
                    // run over content
                    foreach ($xml->channel->item as $newsItem) {
                        $io->comment('Found '.$newsItem->link);
                        if(array_search(['url' => $newsItem->link], $existingNews) === false){
                            $io->comment('Is a new item');
                            $newsEntry = new News();
                            $newsEntry->setSite($newsSource->getName());
                            $newsEntry->setTitle($newsItem->title);
                            $newsEntry->setUrl($newsItem->link);
                            $newsEntry->setDateAdded(new \DateTime());
                            $em->persist($newsEntry);
                        }
                    }
                    $em->flush();
                }
            }
            $io->comment('Parsing readability');
            $command = $this->getApplication()->find('readability:process');
            $arguments = array(

            );
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            if($returnCode == 0) {
                $io->comment('Readability successfully executed');
            }
        }
        $io->success('All commands done.');
    }
}