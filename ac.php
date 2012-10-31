#!/usr/bin/env php
<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

namespace kostajh\ActiveCollabCli;

define("CURRENT_USER",  get_current_user());

// Check to see if requirements are met before proceeding.
$ac_cli = new ActiveCollabCli();

require_once 'vendor/autoload.php';
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabApi\ActiveCollabApi;

$ac = new ActiveCollabApi();
$ac->setAPIUrl($ac_cli->ac_url);
$ac->setKey($ac_cli->ac_token);

$console = new Application();

$console
    ->register('user-tasks')
    ->addOption(
        'project',
        null,
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        'Specify the project to load tasks for',
        null
    )
    ->setDescription('List tasks for the authenticating user.')
    ->setHelp(
        'The <info>user-tasks</info> command will display a list of tasks for the current user.

  <comment>Samples:</comment>
    To run with default options:
      <info>php ac.php user-tasks</info>
    To list tasks for a specific project
      <info>php ac.php user-tasks 150</info>'
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
      $ac_cli = new activeCollabCli();
      if ($projects = $input->getOption('project')) {
          $projects = array_flip($projects);
      } else {
        $projects = unserialize($ac_cli->projects);
        if (!is_array($projects)) {
          $output->writeln("<error>Could not load any projects to query.</error>");

          return FALSE;
        }
      }

      foreach ($projects as $project_id => $name) {
        $ac = new ActiveCollabProject($project_id);

        if ($tasks = $ac->getUserTasks($project_id)) {
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
      });

$console
  ->register('task-info')
  ->setDefinition(array(
      new InputArgument('task', InputArgument::REQUIRED, 'Project ID and Ticket ID', NULL),
    ))
  ->setDescription('Display information about a specific ticket.')
  ->setHelp('
The <info>task-info</info> command displays information about a specific ticket. Information must be provided in the format <comment>project_id:ticket_id</comment>.

<comment>Samples:</comment>
  To display ticket information for ticket 233 in project 150
    <info>php ac.php task-info 150:233</info>
')
  ->setCode(function (InputInterface $input, OutputInterface $output) {
    $project_ticket = $input->getArgument('task');
    if (!$project_ticket) {
      $output->writeln("<error>Please specify a Project number and ticket ID in the format: {project_id}:{ticket_id}</error>");

      return FALSE;
    }
    $project_id = substr($project_ticket, 0, strpos($project_ticket, ':'));
    $ticket_id = substr($project_ticket, strpos($project_ticket, ':') + 1);
    $ac_cli = new activeCollabCli();
    $data = $ac_cli->getTaskInfo($project_id, $ticket_id);

    $info = array();
    if (!is_array($data)) {
      $output->writeln("<info>Project ID:</info> " . $data->project_id);
      $output->writeln("<info>Ticket Name:</info> " . $data->name);
      $output->writeln("<info>Created on:</info> " . $data->created_on);
      $output->writeln("<info>URL:</info> " . $data->permalink);
      $output->writeln("<info>Body: </info>" . trim(strip_tags($data->body), 200));
      isset($data->due_on) ? $output->writeln("<info>Due on:</info> " . $data->due_on) : NULL;
      if (isset($data->tasks) && $data->tasks) {
        $output->writeln("<info>Tasks:</info>");
        foreach ($data->tasks as $task) {
          if ($task->completed_on) {
            $output->writeln("<info>[DONE]</info> " . $task->body);
          } else {
            $text = "<comment>[PENDING]</comment> " . $task->body;
            if ($task->due_on && !$task->completed_on) {
              $text .= "<comment> [" . $task->due_on . "]</comment>";
            }
            $output->writeln($text);
          }

        }
      }

      return;
    }

    });

$console->run();

/**
* Ability to authorize and communicate with the activeCollab 2.x API.
*/
class ActiveCollabCli
{

  /**
   * Constructor
   */
  public function __construct()
  {
    if (!$this->checkRequirements()) {
      return FALSE;
    }
    $config = parse_ini_file('/Users/' . CURRENT_USER . '/.active_collab');
    $this->ac_url = $config['ac_url'];
    $this->ac_token = $config['ac_token'];
    $this->projects = serialize($config['projects']);
  }

  /**
   * Check to see if config file is present and for other requirements.
   */
  public function checkRequirements()
  {
    if (!file_exists('vendor/autoload.php')) {
      print "Please run the install.sh script.\n";

      return FALSE;
    }
    if (!file_exists('/Users/' . CURRENT_USER . '/.active_collab')) {
      print "Please create a ~/.active_collab file.\n";

      return FALSE;
    }
    $file = parse_ini_file('/Users/' . CURRENT_USER . '/.active_collab');
    if (!is_array($file)) {
      print "Could not parse config file.";

      return FALSE;
    }
    if (!isset($file['ac_url']) || !$file['ac_url']) {
      print "Please specify a value for ac_url in your config file!\n";
    }
    if (!isset($file['ac_token']) || !$file['ac_token']) {
      print "Please specify a value for ac_token in your config file!\n";
    }
    if (!isset($file['ac_url']) || !isset($file['ac_token']) || !$file['ac_url'] || !$file['ac_token']) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Displays information about a ticket.
   */
  public function getTaskInfo($project_id, $ticket_id)
  {
    $ch = curl_init();
    $url = $this->ac_url . '?token=' . $this->ac_token . '&path_info=/projects/' . $project_id . '/tickets/' . $ticket_id . '&format=json';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $data = json_decode($response);
    curl_close($ch);

    return $data;
  }

  /**
   * Get user tasks for a given project.
   */
  public function getTasksForProject($project_id)
  {
    if (!is_numeric($project_id)) {
      return FALSE;
    }
    $ch = curl_init();
    $url = $this->ac_url . '?token=' . $this->ac_token . '&path_info=/projects/' . $project_id . '/user-tasks&format=json';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $data = json_decode($response);
    curl_close($ch);
    $tasks = array();
    if (!is_array($data)) {
      return FALSE;
    }
    foreach ($data as $task) {
      if ($task->type == 'Ticket') {
        $tasks[$task->ticket_id] = $task->name;
      } elseif ($task->type == 'Task') {
        $tasks[$task->id] = 'Task: ' . $task->name;
      }
    }

    return $tasks;
  }
}
