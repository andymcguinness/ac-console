<?php

namespace ActiveCollabConsole\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ActiveCollabConsole\ActiveCollabConsole;
use SimplePie;

/**
 * Display recent activities feed.
 * @author Kosta Harlan <kostajh@gmail.com>
 */
class RecentActivitiesCommand extends Command
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
        ->setName('recent-activities')
        ->setDescription('Display recent activities feed.')
        ->setHelp('The <info>recent-activities</info> command displays the recent activities feed.'
        );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Obtaining recent activities...');
        $items = $this->getRecentActivities(FALSE, 5);
        foreach ($items as $item) {
          $output->writeln('<info>' . $item['title'] . '</info>');
          $output->writeln($this->acConsole->cleanText($item['description']));
          $output->writeln('<comment>' . $item['permalink'] . '</comment>');
          $output->writeln('<info>---------------------------------------</info>');
        }
        // print_r($items);
    }

    /**
     * Returns an array of items from the recent activities RSS feed.
     *
     * @param boolean $cache
     * @param int $items
     */
    public function getRecentActivities($cache = FALSE, $items = 10)
    {
      $feed = new SimplePie();
      $rss = $this->acConsole->rss;
      $cacheDir = $this->acConsole->getCacheDir();
      $feed->set_feed_url($rss);
      $feed->enable_cache($cache);
      $feed->set_cache_location($cacheDir . '/rss');
      $feed->init();
      $feed->handle_content_type();
      $feedData = array();
      foreach ($feed->get_items() as $item) {
        $feedItem['permalink'] = $item->get_permalink();
        $feedItem['title'] = $item->get_title();
        $feedItem['description'] = $item->get_description();
        $feedData[] = $feedItem;
      }

      return array_slice($feedData, 0, $items);
    }

}
