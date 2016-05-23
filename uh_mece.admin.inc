<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

/**
 * Callback for configuration form.
 */
function uh_mece_configuration() {
  $form = array();

  // General settings
  $form['uh_mece'] = array(
    '#type' => 'fieldset',
    '#title' => t('General settings'),
    'uh_mece_empty_string_enabled' => array(
      '#type' => 'checkbox',
      '#title' => t('Send empty strings if no value found'),
      '#description' => t('When checked, messages will contain properties that exist but provides empty string.'),
      '#default_value' => variable_get('uh_mece_empty_string_enabled', FALSE),
    ),
    'uhc_mece_expire' => array(
      '#title' => t('Offset expire'),
      '#description' => t('Enter number of seconds when messages should expire. By default it\'s 1 month in seconds.'),
      '#type' => 'textfield',
      '#default_value' => variable_get('uhc_mece_expire', 60*24*30),
    ),
  );

  // REST server connection
  $form['uh_mece_http'] = array(
    '#type' => 'fieldset',
    '#title' => t('REST server connection configuration'),
    'uh_mece_http_endpoint_url' => array(
      '#title' => t('Endpoint URL'),
      '#description' => t('Type in endpoint URL where messages should be sent to.'),
      '#type' => 'textfield',
      '#default_value' => variable_get('uh_mece_http_endpoint_url', ''),
    ),
    'uh_mece_http_method' => array(
      '#type' => 'select',
      '#title' => t('Submission HTTP method'),
      '#description' => t('Select the method you would like to use for sending messages.'),
      '#options' => array(
        'post' => 'POST',
        'put' => 'PUT',
      ),
      '#default_value' => variable_get('uh_mece_http_method', 'post'),
    ),
  );

  // Authentication / identity
  $form['uh_mece_auth'] = array(
    '#type' => 'fieldset',
    '#title' => t('Authentication configuration'),
    'uh_mece_auth_source' => array(
      '#title' => t('Identity: Source'),
      '#description' => t('Which "source" you want to ship the message as.'),
      '#type' => 'textfield',
      '#default_value' => variable_get('uh_mece_auth_source', ''),
    ),
  );

  return system_settings_form($form);
}

/**
 * Validation callback for configuration.
 */
function uh_mece_configuration_validate(&$form, &$form_state) {
  if (!is_numeric($form_state['values']['uhc_mece_expire'])) {
    form_set_error('uhc_mece_expire', t('Expire must be numerical value'));
  }
}
