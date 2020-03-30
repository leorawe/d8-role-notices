<?php

namespace Drupal\lw_role_notices;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Cache\Cache;

/**
 * Simple Notices Manager.
 *
 * This class is set as service in role_notices.services.yml
 * It should be provided to classes via Dependency injection. When it is
 * used in global functions in the .module file we can access it via
 * \Drupal::service().
 *
 * Having a separate class for setting and getting notices means it will easier
 * to change this later on. If we wanted to allow other modules use their own
 * notice manager service we could create a NoticesManagerInterface that this
 * class would implement. The interface would specify all our public methods.
 *
 * @see \Drupal\role_notices\Form\RoleNoticesSettingsForm::create()
 */
class NoticesManager {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Creates a new Notices Manager.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   * @param \Drupal\Core\State\StateInterface $state
   *   Used to get and save notices from site state.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(AccountInterface $user, StateInterface $state, ModuleHandlerInterface $module_handler) {
    $this->user = $user;
    $this->state = $state;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Gets the notices for the current user.
   *
   * @return array
   *   An array of notices for the current user. keyed by role id(rid)
   */
  public function getUserNotices() {
    $user_notices = [];
    if ($all_notices = $this->getAllNotices()) {
      // Get only the notices for the roles of the current user.
      $user_notices = array_intersect_key($all_notices, array_flip($this->user->getRoles()));
      // Allow other modules to alter the notices.
      // This creates the `hook_role_notices_alter` hook. It is documented
      // in `role_notices.api.php`. See the module 'role_notices_weight' which
      // implements this hook.
      $this->moduleHandler->alter('lw_role_notices', $user_notices);
    }
    return $user_notices;
  }

  /**
   * Get notices for all roles.
   *
   * @return array
   *   Any associative array of notices.
   *    - keys are machine names of roles
   *    - values are notices strings
   */
  public function getAllNotices() {
    return $this->state->get('lw_role_notices.notices', []);
  }

  /**
   * Set notices for all roles.
   *
   * The notices are saved using Drupal::state()->set().
   * We don't want this setting to be exportable.
   * If we wanted it to be exportable we would use Drupal::config().
   *
   * @param array $notices
   *   Any associative array of notices.
   *    - keys are machine names of roles.
   *    - values are notices strings.
   */
  public function setAllNotices(array $notices) {
    // Save the updated keys before saving the new notices.
    $updated_keys = $this->getUpdatedNotices($notices);
    $this->state->set('lw_role_notices.notices', $notices);
    /*
     * Delete render tags for updated Notices.
     * This ensures that if a user has a role with an updated notice they will
     * see the new value.
     * It also ensures if they have a role that was deleted they won't see the
     * old notice.
     */
    if ($updated_keys) {
      Cache::invalidateTags($this->getRenderTags($updated_keys));
    }

    // @todo Also delete tags for notices when the Roles are deleted.
  }

  /**
   * Return the rids for any Notices that have been updated.
   *
   * This includes new roles and roles that were deleted.
   *
   * @param array $new_notices
   *   Notices that were just submitted to be saved.
   *   They haven't been saved yet.
   *
   * @return array
   *   Role Ids that have been updated or no longer exist.
   */
  protected function getUpdatedNotices(array $new_notices) {
    $current_notices = $this->getAllNotices();
    $diff = array_diff_assoc($current_notices, $new_notices)
      + array_diff_assoc($new_notices, $current_notices);
    return $diff ? array_keys($diff) : [];
  }

  /**
   * Get the render tags for given rids.
   *
   * @param array $rids
   *   Role Ids.
   *
   * @return string[]
   *   Render tags in the format: 'role_notices:[ROLE_ID]'.
   */
  public function getRenderTags(array $rids) {
    $tags = [];
    if ($rids) {
      foreach ($rids as $rid) {
        $tags[] = 'lw_role_notices:' . $rid;
      }
    }
    return $tags;
  }

}
