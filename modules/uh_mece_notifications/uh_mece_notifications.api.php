<?php

/**
 * @file
 * Contains examples/documentation of hooks provided by this module.
 */

/**
 * Allows to modify the request prior to sending.
 * @param &$request
 *   An associative array having two elements:
 *     - url: The URL address where request will be sent
 *     - options: Options array that will be passed to drupal_http_request()
 */
function hook_uh_mece_notifications_request_alter(&$request) {
  $request['url'] = $request['url'] . '/hi';
  $request['options']['headers']['X-CustomHeader'] = 'Hello';
  $request['options']['timeout'] = 600;
}
