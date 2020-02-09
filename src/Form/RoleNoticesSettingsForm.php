<?php

namespace Drupal\lw_role_notices\Form;

use Drupal\Core\Form\FormBase;
use \Drupal\Core\Form\FormStateInterface;

class RoleNoticesSettingsForm extends FormBase{

  /**
   * (@inheritDoc)
   */
  public function getFormId()
  {
    return 'role_notices_setting_form';
  }

    /**
   * (@inheritDoc)
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['notice'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Notice'),
      '#description' => $this->t('Add a notice'),
      '#default_value'=> \Drupal::service('state')->get('lw_role_notice.test'),
    ];
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
    $notice = $form_state->getValue('notice');
    \Drupal::state()->set('lw_role_notice.test', $notice);
    \Drupal::messenger()->addMessage($this->t('the notices have been ah saved ah saved.'));
  }
}