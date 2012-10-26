#!/usr/bin/env php
<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

define("CURRENT_USER",  get_current_user());

if (!check_requirements()) {
  return FALSE;
}

require_once('vendor/autoload.php');
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$console = new Application();

$console
  ->register('user-tasks')
  ->setDefinition(array(
      new InputArgument('project', InputArgument::OPTIONAL, 'Tasks for a specific project', NULL),
    ))
  ->setDescription('List tasks for the authenticating user.')
  ->setHelp('
The <info>user-tasks</info> command will display a list of tasks for the current user.

<comment>Samples:</comment>
  To run with default options:
    <info>php ac.php user-tasks</info>
  To list tasks for a specific project
    <info>php ac.php user-tasks 150</info>
')
  ->setCode(function (InputInterface $input, OutputInterface $output) {
    $projects = $input->getArgument('project');
    if (!$projects) {
      $projects = unserialize(PROJECTS);
    }
    foreach ($projects as $project_id => $name) {
      $tasks = get_tasks_for_project($project_id);
      $output->writeln("<info>Tasks for Project #$project_id - $name</info>");
      $output->writeln("<info>===========================================</info>");
      if ($tasks) {
        foreach ($tasks as $task_id => $task_name) {
          $output->writeln('<comment>#' . $task_id . ': ' . $task_name . '</comment>');
        }
      }
      else {
        $output->writeln("No tasks found!");
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
    $ch = curl_init();
    $url = AC_URL . '?token=' . AC_TOKEN . '&path_info=/projects/' . $project_id . '/tickets/' . $ticket_id . '&format=json';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $data = json_decode($response);
    curl_close($ch);
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
 * Check to see if config file is present.
 */
function check_requirements()  {
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
  define("AC_URL", $file['ac_url']);
  define("AC_TOKEN", $file['ac_token']);
  define("PROJECTS", serialize($file['projects']));
  return TRUE;
}

/**
 * Display all user tasks for defined projects.
 */
function user_tasks($projects = array()) {
  if (!$projects) {
    $projects = unserialize(PROJECTS);
  }
  foreach ($projects as $project_id => $name) {
    $tasks = get_tasks_for_project($project_id);
    print "Tasks for Project #$project_id - $name\n";
    print "===========================================\n";
    if ($tasks) {
      foreach ($tasks as $task_id => $task_name) {
        print '#' . $task_id . ': ' . $task_name . "\n";
      }
      print "\n";
    }
    else {
      print "No tasks found!\n\n";
    }
  }
}

/**
 * Displays information about a ticket.
 */
function task_info($project_ticket = NULL) {
  if (!$project_ticket) {
    print "Please specify a Project number and ticket ID in the format: {project_id}:{ticket_id}\n";
    return FALSE;
  }
  $project_id = substr($project_ticket, 0, strpos($project_ticket, ':'));
  $ticket_id = substr($project_ticket, strpos($project_ticket, ':') + 1);
  $data = get_task_info($project_id, $ticket_id);
  $info = array();
  if (!is_array($data)) {
    print "Project ID: " . $data->project_id . "\n";
    print "Ticket Name: " . $data->name . "\n";
    print "Created on: " . $data->created_on . "\n";
    print "URL: " . $data->permalink . "\n";
    print "Body: " . strip_tags($data->body) . "\n";
    isset($data->due_on) ? print "Due on: " . $data->due_on . "\n" : NULL;
    if (isset($data->tasks) && $data->tasks) {
      print "Tasks:\n";
      foreach ($data->tasks as $task) {
        if ($task->completed_on) {
          print "- [DONE] ";
        } else {
          print "- [PENDING] ";
        }
        print $task->body;
        if ($task->due_on && !$task->completed_on) {
          print " [" . $task->due_on . "]";
        }
        print "\n";
      }
    }
    return;
  }
}

function get_task_info($project_id, $ticket_id) {
  $ch = curl_init();
  $url = AC_URL . '?token=' . AC_TOKEN . '&path_info=/projects/' . $project_id . '/tickets/' . $ticket_id . '&format=json';
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
function get_tasks_for_project($project_id) {
    $ch = curl_init();
    $url = AC_URL . '?token=' . AC_TOKEN . '&path_info=/projects/' . $project_id . '/user-tasks&format=json';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $data = json_decode($response);
    curl_close($ch);
    $tasks = array();
    if (!is_array($data)) {
      return;
    }
    foreach ($data as $task) {
      if ($task->type == 'Ticket') {
        $tasks[$task->ticket_id] = $task->name;
      }
      else if ($task->type == 'Task') {
        $tasks[$task->id] = 'Task: ' . $task->name;
      }
    }
    return $tasks;
}

