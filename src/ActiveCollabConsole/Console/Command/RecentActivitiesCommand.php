<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;

/**
 * Display recent activities feed.
 * @author Kosta Harlan <kostajh@gmail.com>
 */
class RecentActivitiesCommand extends Command
{

    /**
     * @param ActiveCollabConsole $acConsole
     */
    public function __construct(ActiveCollabConsole $acConsole = null)
    {
        $this->acConsole = $acConsole ?: new ActiveCollabConsole();
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
      $this
        ->setName('recent-activities')
        ->setDescription('Display recent activities feed.')
        ->setHelp('The <info>recent-activities</info> command displays the recent activities feed.'
        );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Obtaining recent activities...');
        $items = $this->acConsole->getRecentActivities();
    }

}
