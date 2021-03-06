<?php
namespace AppBundle\Command;

use AppBundle\Entity\News;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $token = $this->getContainer()->getParameter('readability_key');

        $em = $this->getContainer()->get('doctrine')->getManager();
        $existingNews = $em->getRepository('AppBundle:News')->findAllUnprocessed();
        $io = new SymfonyStyle($input, $output);
        $io->title('Parsing news from Redability');
        $io->comment('Found '.count($existingNews).' unprocessed news.');

        $client   = $this->getContainer()->get('guzzle.client.api_readability');

        foreach($existingNews as $unprocessedNew){
            // make call to api
            $response = null;
            try{
                $response = $client->get('?url='.$unprocessedNew->getUrl().'&token='.$token);
            } catch (\Exception $e){
                $io->warning('Exception loading url '.$unprocessedNew->getUrl());
                $io->warning($e->getMessage());
                $unprocessedNew->setEnabled(false);
                $em->persist($unprocessedNew);
                $em->flush();
                $io->warning('New has been disabled');
                continue;
            }

            if(!is_null($response) && $response->getStatusCode() == 200){
                $data = json_decode($response->getBody(), true);
                if(!$this->isValidImage($data) || !$this->hasValidTitle($data)){
                    $io->warning('New has invalid image or title, disabling');
                    $unprocessedNew->setEnabled(false);
                    $em->persist($unprocessedNew);
                    $em->flush();
                } else {
                    $unprocessedNew->setContent($data['content']);
                    $unprocessedNew->setLeadImageUrl($data['lead_image_url']);
                    $em->persist($unprocessedNew);
                    $em->flush();
                    $io->comment('Saving processed new');
                }
            }
        }
        $io->success('Readability completed');
    }

    protected function isValidImage($data){
        if(empty($data['content']) || empty($data['lead_image_url'])
        || $data['lead_image_url'] == 'http://www.quo.es/var/quo/storage/images/auxiliar/right/app-quo/926526-2-esl-ES/app-quo_promocionado.png'){
            return false;
        }
        list($with, $height, $type, $attr) = getimagesize($data['lead_image_url']);
        if($with * $height < 40000){
            return false;
        }
        return true;
    }

    protected function hasValidTitle($data){
        $bad = [
            'samsung', 'apple', 'google', 'microsoft', 'bq', 'sony', 'presenta', 'smartphone',
        'lg', 'huawei', 'cazando gangas', 'iphone', 'alcatel', 'motorola', 'android', 'ios'
        ];
        if(empty($data['content'])){
            return false;
        }

        $search = $data['title'];

        $matches = array_filter($bad, function($var) use ($search) { return preg_match("/\b$search\b/i", $var); });

        return count($matches)>0?false:true;
    }
}
