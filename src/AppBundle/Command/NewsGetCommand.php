<?php
/**
 * Created by PhpStorm.
 * User: jesus
 * Date: 18/02/2016
 * Time: 21:44
 */

namespace AppBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsGetCommand extends Command
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
        $commandsToRun = [
            'microsiervos:scrape'
            , 'montt:scrape'
            , 'muy:scrape'
            , 'quo:scrape'
            , 'xataka:scrape'
        ];

        $output->writeln('Running commands to get news');
        foreach($commandsToRun as $commandLiteral) {
            $command = $this->getApplication()->find($commandLiteral);
            $arguments = array(

            );
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            if($returnCode == 0) {
                $output->writeln($commandLiteral.' successfully executed');
            }
        }

        $output->writeln('All commands done.');
    }
}