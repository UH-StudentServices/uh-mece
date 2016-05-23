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
  $form['uh_mece'] = array(
    '#type' => 'fieldset',
    '#title' => t('REST server connection configuration'),
    'uh_mece_http_host' => array(
      '#title' => t('Endpoint URL'),
      '#description' => t('Type in endpoint URL where messages should be sent to.'),
      '#type' => 'textfield',
      '#default_value' => variable_get('uh_mece_http_host', ''),
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
  return system_settings_form($form);
}
