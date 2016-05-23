# University of Helsinki, MECE Notifications
Provides ability to sending notification messages.

## Example usage

```php

/**
 * Implements hook_node_insert().
 */
function mymodule_node_insert($node) {
  if ($node->type == 'event') {
    // Get instance
    $recipients = ['matti', 'liisa'];
    $message = uh_mece_notifications_get_instance($recipients);

    $message_text = uh_mece_notifications_get_multilingual_value();
    $message_text->setValue('Luotiin uusi tapahtuma', 'fi');
    $message_text->setValue('Created new event', 'en');
    $message_text->setValue('Ny händelse skapades', 'sv');
    $message->setMessage($message_text);

    $link_text = uh_mece_notifications_get_multilingual_value();
    $link = uh_mece_notifications_get_multilingual_value();
    $uri = entity_uri('node', $node);
    foreach (array('fi', 'en', 'sv') as $lang) {
      if (isset($node->title_field[$lang]['value'])) {
        $link_text->setValue($node->title_field[$lang]['value'], $lang);
      }
      else {
        $link_text->setValue($node->title, $lang);
      }
      $link->setValue(url($uri['path'], array('language' => $lang)), $lang);
    }
    $message->setLinkText($link_text);
    $message->setLink($link);
    
    // Send message
    $response = uh_mece_notifications_send($message);
    if ($response->code == 200) {
      drupal_set_message('Message sent successfully.');
    }
  }
}

```