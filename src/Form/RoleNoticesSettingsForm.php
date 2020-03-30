<?php

namespace Drupal\lw_role_notices\Form;

use Drupal\Core\Form\FormBase;
use Drupal\role_notices\NoticesManager;
use \Drupal\Core\Form\FormStateInterface;

class RoleNoticesSettingsForm extends FormBase{

  /**
   * (@inheritDoc)
   */
  public function getFormId()
  {
    return 'lw_role_notices_setting_form';
  }

    /**
   * (@inheritDoc)
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $role_names = user_role_names(TRUE);
    /*
    * Using an empty container will make our values come back as an array
    */
    $form['notices'] = [
      '#tree' => TRUE,
    ];
    $notices = \Drupal::service('lw_role_notices.notice_manager')->getAllNotices();
    // Create 1 text area for each role
    foreach ($role_names as $role_id => $role_name){
        $form['notices'][$role_id] = [
          '#type' => 'textarea',
          '#title' => $role_name,
          '#description' => $this->t('Add a notice for the <strong>@role</strong>', ['@role' => $role_name]),
          '#default_value'=> isset($notices[$role_id])? $notices[$role_id]:'',
        ];
      }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save notices'),
    ];
    return $form;
  }

  /**
   * (@inheritDoc)
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $notices = $form_state->getValue('notices');
    \Drupal::service('lw_role_notices.notice_manager')->setAllNotices($notices);
    \Drupal::messenger()->addMessage($this->t('Notices have been saved.'));
  }
}