<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;

/**
 * Lists recent status messages.
 * @author Kosta Harlan <kostajh@gmail.com>
 */
class StatusCommand extends Command
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
      // @todo Add option for limiting number of messages
      $this
        ->setName('status')
        ->setDescription('Display recent status messages.')
        ->setHelp('The <info>status</info> command displays recent status messages.
        ');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($statuses = $this->acConsole->api('listStatusMessages')) {
          foreach ($statuses as $status) {
            $output->writeln("<info>" . $status['created_by']['name'] . ": </info>" . $status['message']);
            $output->writeln("<comment>    Posted " . $status['created_on'] . "</comment>");
          }
        }
        else {
          $output->writeln("<error>Could not access status information!</error>");
        }
    }

}
