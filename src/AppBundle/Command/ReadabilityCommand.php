<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadabilityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('readability:process')
            ->setDescription('Fetch readability');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = '4f9cf3693b36e0fe4c91bb3fca3eaa9236c926bd';

        $em = $this->getContainer()->get('doctrine')->getManager();
        $existingNews = $em->getRepository('AppBundle:News')->findAllUnprocessed();
        $output->writeln('Found '.count($existingNews).' unprocessed news.');

        $client   = $this->getContainer()->get('guzzle.client.api_readability');

        foreach($existingNews as $unprocessedNew){
            // make call to api
            $response = $client->get('?url='.urlencode($unprocessedNew->getUrl()).'&token='.$token);

            if($response->getStatusCode() == 200){
                $data = json_decode($response->getBody(), true);
                $unprocessedNew->setContent($data['content']);
                $unprocessedNew->setLeadImageUrl($data['lead_image_url']);
                $em->persist($unprocessedNew);
            }
        }
        $em->flush();
    }
}
