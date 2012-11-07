<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;

/**
 * Obtains information about the current activeCollab instance.
 * @author Kosta Harlan <kostajh@gmail.com>
 */
class InfoCommand extends Command
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
        ->setName('info')
        ->setDescription('Display API information about the current activeCollab instance')
        ->setHelp('The <info>info</info> command displays API information for the activeCollab instance.
        ');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($version = $this->acConsole->api('getVersion')) {
          $output->writeln("<info>API Version:</info> " . $version->api_version);
          $output->writeln("<info>System Version:</info> " . $version->system_version);
          $output->writeln("<info>System Edition:</info> " . $version->system_edition);
          $output->writeln("<info>Logged-in User:</info> " . $version->logged_user);
          $output->writeln("<info>Read/write Access:</info> " . (($version->read_only === 0) ? 'Read/write' : 'Read-only'));
        }
        else {
          $output->writeln("<error>Could not access version information!</error>");
        }
    }

}
