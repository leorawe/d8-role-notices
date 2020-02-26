<?php

namespace Drupal\lw_role_notices\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Session\AccountInterface;
use Drupal\role_notices\NoticesManager;
use Drupal\Core\Access\AccessResult;

/**
 *
 * Provide a Notices Block
 *
 * @Block(
 * id = "role_notices",
 * admin_label = @Translation("LW Role Notices")
 * )
 *
 * @link https://drupal.org/node/1882526
 */
class RoleNoticesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account)
  {
    return AccessResult::allowedIf($account->isAuthenticated());
  }

    /**
   * {@inheritdoc}
   */
  public function build(){
    $all_notices = \Drupal::state()->get('lw_role_notices.notices',[]);
    $user_roles = \Drupal::currentUser()->getRoles();
    $user_notices = array_intersect_key($all_notices, array_flip($user_roles));
    return [
      '#theme' => 'item_list',
      '#items' => $user_notices,
//      '#markup' => 'bob',
    ];
  }
}