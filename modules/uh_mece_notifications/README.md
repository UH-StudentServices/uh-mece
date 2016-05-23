# University of Helsinki, MECE Notifications
Provides ability to sending notification messages.

## Example usage

```php

// Get instance
$recipients = ['matti', 'liisa'];
$message = uh_mece_nofications_get_instance($recipients);

// Send message
$response = uh_mece_notifications_send($message);
if ($response->code == 200) {
  drupal_set_message('Message sent successfully.');
}

```