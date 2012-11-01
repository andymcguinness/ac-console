<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;
use ActiveCollabApi\ActiveCollabApi;

/**
* @author Kosta Harlan <kostajh@gmail.com>
*/
class UserTasksCommand extends Command
{

    /**
     * @param ActiveCollabConsole           $ac_console
     */
    public function __construct(ActiveCollabConsole $ac_console = null)
    {
        $this->ac_console = $ac_console ?: new ActiveCollabConsole();
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('user-tasks')
            ->setDescription('List tasks for the authenticating user.')
            ->setDefinition(array(
                new InputOption('project', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Specify the project to load tasks for', null),
            ))
            ->setHelp(
                    'The <info>user-tasks</info> command will display a list of tasks for the current user.

              <comment>Samples:</comment>
                To run with default options:
                  <info>php ac.php user-tasks</info>
                To list tasks for a specific project
                  <info>php ac.php user-tasks 150</info>'
                );
    }


    /**
* @see Command
*/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      if ($projects = $input->getOption('project')) {
          $projects = array_flip($projects);
      }
      else {
        $projects = unserialize($this->ac_console->projects);
        if (!is_array($projects)) {
          $output->writeln("<error>Could not load any projects to query.</error>");

          return FALSE;
        }
      }

      foreach ($projects as $project_id => $name) {
        if ($tasks = $this->ac_console->getUserTasks($project_id)) {
          $output->writeln("<info>===========================================</info>");
          $project_header = ($name) ? $project_id . ' - ' . $name : $project_id;
          $output->writeln("<info>Tasks for Project #$project_header</info>");
          $output->writeln("<info>===========================================</info>");
          if ($tasks) {
            foreach ($tasks as $task) {
              if ($task->type == 'Ticket') {
                $output->writeln('<comment>#' . $task->ticket_id . ': ' . $task->name . '</comment>');
              } else {
                // @todo Display tasks
              }
            }
          } else {
            $output->writeln("No tasks found!");
          }
        } else {
          $output->writeln("<error>Could not load any tasks for project #$project_id</error>");
        }

      }
    }
}