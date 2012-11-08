<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;

/**
 * Displays project tasks for the authenticating user.
 *
 * @author Kosta Harlan <kostajh@gmail.com>
 */
class UserTasksCommand extends Command
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
        ->setName('user-tasks')
        ->setDescription('List tasks for the authenticating user.')
        ->setDefinition(array(
            new InputArgument('project', InputArgument::OPTIONAL, 'Project ID', NULL),
        ))
        ->setHelp('The <info>user-tasks</info> command will display a list of tasks for the current user.

          <comment>Samples:</comment>
            To run with default options:
              <info>ac user-tasks</info>
            To list tasks for a specific project
              <info>ac user-tasks 150</info>'
        );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      if ($projects = (array)$input->getArgument('project')) {
          $projects = array_flip($projects);
      } else {
        $projects = unserialize($this->acConsole->projects);
        if (!is_array($projects)) {
          $output->writeln("<error>Could not load any projects to query.</error>");

          return false;
        }
      }

      foreach ($projects as $projectId => $name) {
        if ($tasks = $this->acConsole->getUserTasks($projectId)) {
          $output->writeln("<info>===========================================</info>");
          $projectHeader = ($name) ? $projectId . ' - ' . $name : $projectId;
          $output->writeln("<info>Tasks for Project #$projectHeader</info>");
          $output->writeln("<info>===========================================</info>");
          // Group tasks by milestone
          $groupedTasks = array();
          $milestones = array();
          foreach ($tasks as $task) {
            if (isset($task['milestone_id']) && $task['milestone_id'])  {
              $groupedTasks[$task['milestone_id']][] = $task;
              if (!isset($milestones[$task['milestone_id']])) {
                $milestones[$task['milestone_id']] = $this->acConsole->getMilestoneById($projectId, $task['milestone_id']);
              }
            } else {
              $groupedTasks['no_milestone'][] = $task;
            }
          }

          if ($groupedTasks) {
            foreach ($groupedTasks as $milestone => $tasks) {
              // Get milestone data
              if (is_numeric($milestone)) {
                $completed = !empty($milestones[$milestone]['completed_on']) ? ' [COMPLETED]' : NULL;
                $output->writeln('<question>Milestone: ' . $milestones[$milestone]['name'] . $completed . '</question>');
              }
              foreach ($tasks as $task) {
                if ($task['type'] == 'Ticket') {
                  $output->writeln('  <comment>#' . $task['ticket_id'] . ': ' . $task['name'] . '</comment>');
                }
                elseif ($task['type'] == 'Task') {
                  // $output->writeln('    <comment>TASK: ' . $task->body . '</comment>');
                }
              }
            }
          } else {
            $output->writeln("No tasks found!");
          }
        } else {
          $output->writeln("<error>Could not load any tasks for project #$projectId</error>");
        }

      }
    }

}
