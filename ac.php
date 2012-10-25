#!/usr/bin/env php
<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

define("CURRENT_USER",  get_current_user());

/**
 * Setup
 */
if (!check_requirements()) {
  return FALSE;
}

/**
 * Process commands
 */
if (isset($argv[1])) {
  switch ($argv[1]) {
  case 'user-tasks':
    if (isset($argv[2])) {
      $projects[$argv[2]] = $argv[2];
      user_tasks($projects);
    } else {
      user_tasks();
    }
    break;
  case 'task-info':
      task_info(isset($argv[2]) ? $argv[2] : NULL);
    break;
  default:
    print "Command $argv[1] was not recognized!\n";
    break;
  }
} else {
  print "Please specify a command!\n";
  print "Available commands:\n";
  $commands = available_commands();
  foreach ($commands as $command) {
    print "- " . $command['name'] . "\n    " . $command['description'] . "\n    Example: " . $command['example'] . "\n";
  }
}


/**
 * Check to see if config file is present.
 */
function check_requirements()  {
  if (!file_exists('/Users/' . CURRENT_USER . '/.active_collab')) {
    print "Please create a ~/.active_collab file.\n";
    return FALSE;
  }
  $file = parse_ini_file('/Users/' . CURRENT_USER . '/.active_collab');
  if (!is_array($file)) {
    print "Could not parse config file.";
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
  $ch = curl_init();
  $url = AC_URL . '?token=' . AC_TOKEN . '&path_info=/projects/' . $project_id . '/tickets/' . $ticket_id . '&format=json';
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $response = curl_exec($ch);
  $data = json_decode($response);
  curl_close($ch);
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
    }
    return $tasks;
}

function available_commands() {
  return array(
    'user-tasks' => array(
      'name' => 'user-tasks',
      'description' => 'Displays tasks for the authenticating user.',
      'example' => 'ac user-tasks',
    ),
    'task-info' => array(
      'name' => 'task-info',
      'description' => "Displays information about a specific ticket.
      Information must be provided in the format {project_id}:{ticket_id},
      without the braces.",
      'example' => 'ac task-info 150:233',
    ),
  );
}
