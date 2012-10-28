activeCollab CLI
================

A command-line interface for activeCollab 2.x.

## Installation

1. Clone this repo.
2. `cd /path/to/ac_cli`
3. `curl http://getcomposer.org/installer | php`
4. `php composer.phar install`

## Usage

Type "php ac.php" to see a list of commands.

## Configuration

Your `~/.active_collab` file contains information for connecting with AC.

The file should look something like:

    ac_url = https://{site_url}/api.php
    ac_token = {token}
    projects[10] = Project I care about
    projects[25] = Another project

Obtain your `ac_token` by logging into activeCollab and navigating to Profile > API Settings.
