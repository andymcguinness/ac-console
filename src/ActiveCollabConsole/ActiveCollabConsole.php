<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

namespace ActiveCollabConsole;
use ActiveCollabApi\ActiveCollabApi;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Provides methods for interacting with the ActiveCollabApi.
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
      return false;
    }
  }

  /**
   * Check to see if config file is present and for other requirements.
   *
   * @return true if all requirements pass, false otherwise.
   */
  public function checkRequirements()
  {
    $currentUser = get_current_user();
    $fs = new Filesystem();
    $configFile = '/Users/' . $currentUser . '/.active_collab';

    if (!$fs->exists($configFile)) {
      print "Please create a ~/.active_collab file.\n";
      return false;
    }

    $yaml = new Parser();

    try {
        $file = $yaml->parse(file_get_contents($configFile));
        if (!isset($file['ac_url']) || !$file['ac_url']) {
          print "Please specify a value for ac_url in your config file!\n";
        } else {
          parent::setAPIUrl($file['ac_url']);
        }
        if (!isset($file['ac_token']) || !$file['ac_token']) {
          print "Please specify a value for ac_token in your config file!\n";
        } else {
          parent::setKey($file['ac_token']);
        }
        if (!isset($file['ac_url']) || !isset($file['ac_token']) || !$file['ac_url'] || !$file['ac_token']) {
          return false;
        }
        if (isset($file['projects'])) {
          $this->projects = serialize($file['projects']);
        }
    } catch (ParseException $e) {
        printf("Unable to parse the YAML string: %s", $e->getMessage());
        return false;
    }

    return true;
  }

  /**
   * Get a list of assignees for a task.
   *
   * @param object $ticket
   * @return array
   *         An array of names and user IDs, structured by responsibility.
   *         For example:
   *           array('responsible' => array(10 => 'Some name'), 'assigned' =>
   *            array(12 => 'Someone else', 13 => 'another person');
   *
   */
  public function getAssignees($ticket) {
    if (!is_object($ticket)) {
      return false;
    }
    $assignees = $ticket->assignees;
    $ticketId = $ticket->ticket_id;
    $users = array('assigned' => null, 'responsible' => null);
    foreach ($assignees as $assignee) {
      // Obtain the name for each assignee.
      // @todo call out to the API.
      if ($assignee->is_owner) {
        $users['responsible'] = array('id' => $assignee->user_id, 'name' => 'some name');
      } else {
        $users['assigned'][] = array('id' => $assignee->user_id, 'name' => 'another name');
      }
    }
    return $users;
  }

}
