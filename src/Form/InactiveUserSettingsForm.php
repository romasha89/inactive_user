<?php

/**
 * @file
 * Contains Drupal\inactive_user\Form\InactiveUserSettingsForm.
 */

namespace Drupal\inactive_user\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LoginRedirectSettingsForm.
 *
 * @package Drupal\inactive_user\Form
 */
class InactiveUserSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'inactive_user.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'inactive_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('inactive_user.settings');
    $period = array(
      604800,
      1209600,
      1814400,
      2419200,
      2592000,
      7776000,
      15552000,
      23328000,
      31536000,
      47088000,
      63072000,
    );
    $period = array(0 => 'disabled') + array_combine($period, array_map(
      array(\Drupal::service('date.formatter'), 'formatInterval'),
      $period,
      $period
      ));

    $warn_period = array(
      86400,
      172800,
      259200,
      604800,
      1209600,
      1814400,
      2592000,
    );
    $warn_period = array(0 => 'disabled') + array_combine($warn_period, array_map(
        array(\Drupal::service('date.formatter'), 'formatInterval'),
        $warn_period,
        $warn_period
      ));

    $mail_variables = ' %username, %useremail, %lastaccess, %period, %sitename, %siteurl';

    // Set administrator e-mail.
    $form['inactive_user_admin_email_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => t('Administrator e-mail'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['inactive_user_admin_email_fieldset']['inactive_user_admin_email'] = array(
      '#type' => 'textfield',
      '#title' => t('E-mail addresses'),
      '#default_value' => _inactive_user_admin_mail(),
      '#description' => t('Supply a comma-separated list of e-mail addresses that will receive administrator alerts. Spaces between addresses are allowed.'),
      '#maxlength' => 256,
      '#required' => TRUE,
    );

    // Inactive user notification.
    $form['inactive_user_notification'] = array(
      '#type' => 'fieldset',
      '#title' => t('Inactive user notification'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['inactive_user_notification']['inactive_user_notify_admin'] = array(
      '#type' => 'select',
      '#title' => t("Notify administrator when a user hasn't logged in for more than"),
      '#default_value' => $config->get('inactive_user_notify_admin'),
      '#options' => $period,
      '#description' => t("Generate an email to notify the site administrator that a user account hasn't been used for longer than the specified amount of time.  Requires crontab."),
    );
    $form['inactive_user_notification']['inactive_user_notify'] = array(
      '#type' => 'select',
      '#title' => t("Notify users when they haven't logged in for more than"),
      '#default_value' => $config->get('inactive_user_notify'),
      '#options' => $period,
      '#description' => t("Generate an email to notify users when they haven't used their account for longer than the specified amount of time.  Requires crontab."),
    );

    $form['inactive_user_notification']['inactive_user_notify_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Body of user notification e-mail'),
      '#default_value' => $config->get('inactive_user_notify_text') ? $config->get('inactive_user_notify_text') : _inactive_user_mail_text('notify_text'),
      '#cols' => 70,
      '#rows' => 10,
      '#description' => t('Customize the body of the notification e-mail sent to the user. Available variables are: @vars', array('@vars' => $mail_variables)),
      '#required' => TRUE,
    );

    // Automatically block inactive users.
    $form['block_inactive_user'] = array(
      '#type' => 'fieldset',
      '#title' => t('Automatically block inactive users'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['block_inactive_user']['inactive_user_auto_block_warn'] = array(
      '#type' => 'select',
      '#title' => t('Warn users before they are blocked'),
      '#default_value' => $config->get('inactive_user_auto_block_warn') ? $config->get('inactive_user_auto_block_warn') : _inactive_user_mail_text('block_warn_text'),
      '#options' => $warn_period,
      '#description' => t('Generate an email to notify a user that his/her account is about to be blocked.'),
    );
    $form['block_inactive_user']['inactive_user_block_warn_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Body of user warning e-mail'),
      '#default_value' => $config->get('inactive_user_block_warn_text') ? $config->get('inactive_user_block_warn_text') : _inactive_user_mail_text('block_warn_text'),
      '#cols' => 70,
      '#rows' => 10,
      '#description' => t('Customize the body of the notification e-mail sent to the user when their account is about to be blocked.
        Available variables are: @vars', array('@vars' => $mail_variables)),
      '#required' => TRUE,
    );
    $form['block_inactive_user']['inactive_user_auto_block'] = array(
      '#type' => 'select',
      '#prefix' => '<div><hr></div>', /* For visual clarity. */
      '#title' => t("Block users who haven't logged in for more than"),
      '#default_value' => $config->get('inactive_user_auto_block'),
      '#options' => $period,
      '#description' => t("Automatically block user accounts that haven't been used in the specified amount of time.  Requires crontab."),
    );
    $form['block_inactive_user']['inactive_user_notify_block'] = array(
      '#type' => 'checkbox',
      '#title' => t('Notify user'),
      '#default_value' => $config->get('inactive_user_notify_block'),
      '#description' => t('Generate an email to notify a user that his/her account has been automatically blocked.'),
    );

    $form['block_inactive_user']['inactive_user_block_notify_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Body of blocked user account e-mail'),
      '#default_value' => $config->get('inactive_user_block_notify_text') ? $config->get('inactive_user_block_notify_text') : _inactive_user_mail_text('block_notify_text'),
      '#cols' => 70,
      '#rows' => 10,
      '#description' => t('Customize the body of the notification e-mail sent to the user when their account has been blocked.
        Available variables are: @vars', array('@vars' => $mail_variables)),
      '#required' => TRUE,
    );
    $form['block_inactive_user']['inactive_user_notify_block_admin'] = array(
      '#type' => 'checkbox',
      '#title' => t('Notify administrator'),
      '#default_value' => $config->get('inactive_user_notify_block_admin'),
      '#description' => t('Generate an email to notify the site administrator when a user is automatically blocked.'),
    );

    // Automatically delete inactive users.
    $form['delete_inactive_user'] = array(
      '#type' => 'fieldset',
      '#title' => t('Automatically delete inactive users'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['delete_inactive_user']['inactive_user_auto_delete_warn'] = array(
      '#type' => 'select',
      '#title' => t('Warn users before they are deleted'),
      '#default_value' => $config->get('inactive_user_auto_delete_warn'),
      '#options' => $warn_period,
      '#description' => t('Generate an email to notify a user that his/her account is about to be deleted.'),
    );
    $form['delete_inactive_user']['inactive_user_delete_warn_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Body of user warning e-mail'),
      '#default_value' => $config->get('inactive_user_delete_warn_text') ? $config->get('inactive_user_delete_warn_text') : _inactive_user_mail_text('delete_warn_text'),
      '#cols' => 70,
      '#rows' => 10,
      '#description' => t('Customize the body of the notification e-mail sent to the user when their account is about to be deleted.
        Available variables are: @vars', array('@vars' => $mail_variables)),
      '#required' => TRUE,
    );
    $form['delete_inactive_user']['inactive_user_auto_delete'] = array(
      '#type' => 'select',
      '#prefix' => '<div><hr></div>', /* For visual clarity. */
      '#title' => t("Delete users who haven't logged in for more than"),
      '#default_value' => $config->get('inactive_user_auto_delete'),
      '#options' => $period,
      '#description' => t("Automatically delete user accounts that haven't been used in the specified amount of time.  Warning, user accounts are permanently deleted, with no ability to undo the action!  Requires crontab."),
    );
    $form['delete_inactive_user']['inactive_user_preserve_content'] = array(
      '#type' => 'checkbox',
      '#title' => t('Preserve users that own site content'),
      '#default_value' => $config->get('inactive_user_preserve_content'),
      '#description' => t('Select this option to never delete users that own site content.  If you delete a user that owns content on the site, such as a user that created a node or left a comment, the content will no longer be available via the normal Drupal user interface.  That is, if a user creates a node or leaves a comment, then the user is deleted, the node and/or comment will no longer be accesible even though it will still be in the database.'),
    );
    $form['delete_inactive_user']['inactive_user_notify_delete'] = array(
      '#type' => 'checkbox',
      '#title' => t('Notify user'),
      '#default_value' => $config->get('inactive_user_notify_delete'),
      '#description' => t('Generate an email to notify a user that his/her account has been automatically deleted.'),
    );
    $form['delete_inactive_user']['inactive_user_delete_notify_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Body of deleted user account e-mail'),
      '#default_value' => $config->get('inactive_user_delete_notify_text') ? $config->get('inactive_user_delete_notify_text') : _inactive_user_mail_text('delete_notify_text'),
      '#cols' => 70,
      '#rows' => 10,
      '#description' => t('Customize the body of the notification e-mail sent to the user when their account has been deleted.
        Available variables are: @vars', array('@vars' => $mail_variables)),
      '#required' => TRUE,
    );
    $form['delete_inactive_user']['inactive_user_notify_delete_admin'] = array(
      '#type' => 'checkbox',
      '#title' => t('Notify administrator'),
      '#default_value' => $config->get('inactive_user_notify_delete_admin'),
      '#description' => t('Generate an email to notify the site administrator when a user is automatically deleted.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $valid_email = $form_state->getValue('inactive_user_admin_email');
    $mails = explode(',', $valid_email);
    $count = 0;
    $invalid = array();
    foreach ($mails as $mail) {
      if ($mail && !\Drupal::service('email.validator')->isValid(trim($mail))) {
        $invalid[] = $mail;
        $count++;
      }
    }
    if ($count == 1) {
      $form_state->setErrorByName('inactive_user_admin_email', t('%mail is not a valid e-mail address', array('%mail' => $invalid[0])));
    }
    elseif ($count > 1) {
      $form_state->setErrorByName('inactive_user_admin_email', t('The following e-mail addresses are invalid: %mail', array('%mail' => implode(', ', $invalid))));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    foreach ($values as $key => $value) {
      $this->config('inactive_user.settings')
        ->set($key, $value)
        ->save();
    }
  }

}
