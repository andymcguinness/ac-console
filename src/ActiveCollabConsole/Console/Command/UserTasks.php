<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;

/**
* @author Kosta Harlan <kostajh@gmail.com>
*/
class UserTasks extends Command
{
    /**
* @see Command
*/
    protected function configure()
    {
        $this
            ->setName('compile')
            ->setDescription('Compiles the fixer as a phar file')
        ;
    }

    /**
* @see Command
*/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $compiler = new Compiler();
        $compiler->compile();
    }
}
