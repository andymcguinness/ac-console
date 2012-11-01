<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;

/**
 * Obtains the current version of the activeCollab API.
 * @author Kosta Harlan <kostajh@gmail.com>
 */
class GetVersionCommand extends Command
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
        ->setName('get-version')
        ->setDescription('Display activeCollab API version.')
        ->setHelp('The <info>get-version</info> command displays the version for the activeCollab instance.
        ');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($version = $this->acConsole->getVersion()) {
          $output->writeln("<info>API Version</info> " . $version->api_version);
          $output->writeln("<info>System Version</info> " . $version->system_version);
          $output->writeln("<info>System Edition</info> " . $version->system_edition);
        }
        else {
          $output->writeln("<error>Could not access version information!</error>");
        }
    }

}
