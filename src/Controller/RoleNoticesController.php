<?php

namespace Drupal\lw_role_notices\Controller;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A controller().
 *
 */
class RoleNoticesController  {

  /*
  * use Trait blah blah
  *
  */
  use StringTranslationTrait;

  /**
   *
   * @return array
   *   Render array.
   */
public function page() {
  $build = [
    '#type' => 'markup',
    '#markup' => $this->t( 'Hello Peeps'),
  ];
  return $build;
  }
}