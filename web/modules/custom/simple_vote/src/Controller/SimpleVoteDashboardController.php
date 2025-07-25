<?php

namespace Drupal\simple_vote\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for the Simple Vote Dashboard.
 */
class SimpleVoteDashboardController extends ControllerBase {

  public function dashboard() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('
        <h2>Welcome to the Simple Vote admin panel</h2>
        <p>This is where you can set up a dashboard to visualize the results of your polls. You can display key metrics, charts, and insights to better understand user engagement and voting trends. Use this space to monitor responses, analyze data, and make informed decisions based on real-time feedback.</p>
      '),
    ];
  }

}
