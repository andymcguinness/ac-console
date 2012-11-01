<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

namespace ActiveCollabConsole;
use ActiveCollabApi\ActiveCollabApi;

/**
 *
 */
class ActiveCollabConsole extends ActiveCollabApi
{

  const VERSION = '0.1';

  /**
   * Constructor
   */
  public function __construct()
  {
    if (!$this->checkRequirements()) {
      return FALSE;
    }
    $current_user = get_current_user();
    $config = parse_ini_file('/Users/' . $current_user . '/.active_collab');
    $this->projects = serialize($config['projects']);
    parent::setKey($config['ac_token']);
    parent::setAPIUrl($config['ac_url']);

  }

  /**
   * Check to see if config file is present and for other requirements.
   */
  public function checkRequirements()
  {
    $current_user = get_current_user();
    if (!file_exists('vendor/autoload.php')) {
      print "Please run the install.sh script.\n";

      return FALSE;
    }
    if (!file_exists('/Users/' . $current_user . '/.active_collab')) {
      print "Please create a ~/.active_collab file.\n";

      return FALSE;
    }
    $file = parse_ini_file('/Users/' . $current_user . '/.active_collab');
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

}

// $console = new Application();

// $console
//     ->register('user-tasks')
//     ->addOption(
//         'project',
//         null,
//         InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
//         'Specify the project to load tasks for',
//         null
//     )
//     ->setDescription('List tasks for the authenticating user.')
//     ->setHelp(
//         'The <info>user-tasks</info> command will display a list of tasks for the current user.

//   <comment>Samples:</comment>
//     To run with default options:
//       <info>php ac.php user-tasks</info>
//     To list tasks for a specific project
//       <info>php ac.php user-tasks 150</info>'
//     )
//     ->setCode(function (InputInterface $input, OutputInterface $output) {
//       $ac_cli = new activeCollabCli();
//       if ($projects = $input->getOption('project')) {
//           $projects = array_flip($projects);
//       } else {
//         $projects = unserialize($ac_cli->projects);
//         if (!is_array($projects)) {
//           $output->writeln("<error>Could not load any projects to query.</error>");

//           return FALSE;
//         }
//       }

//       foreach ($projects as $project_id => $name) {
//         $ac = new ActiveCollabProject($project_id);

//         if ($tasks = $ac->getUserTasks($project_id)) {
//           $output->writeln("<info>===========================================</info>");
//           $project_header = ($name) ? $project_id . ' - ' . $name : $project_id;
//           $output->writeln("<info>Tasks for Project #$project_header</info>");
//           $output->writeln("<info>===========================================</info>");
//           if ($tasks) {
//             foreach ($tasks as $task) {
//               if ($task->type == 'Ticket') {
//                 $output->writeln('<comment>#' . $task->ticket_id . ': ' . $task->name . '</comment>');
//               } else {
//                 // @todo Display tasks
//               }
//             }
//           } else {
//             $output->writeln("No tasks found!");
//           }
//         } else {
//           $output->writeln("<error>Could not load any tasks for project #$project_id</error>");
//         }

//       }
//       });

