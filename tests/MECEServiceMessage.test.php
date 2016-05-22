<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

require_once __DIR__ . '/../src/MECEServiceMessage.php';

/**
 * Class MECEServiceMessageTest
 *
 * @TODO Test Priority
 * @TODO Test Deadline
 * @TODO Test Expiration
 * @TODO Test Submitted
 * @TODO Test SourceId
 * @TODO Test Heading
 * @TODO Test Link
 * @TODO Test LinkText
 * @TODO Test Message
 * @TODO Test AvatarImageUrl
 */
class MECEServiceMessageTest extends PHPUnit_Framework_TestCase {

  private $recipients = array('user1', 'user2', 'user3', 'user4');
  private $source;

  public function setUp() {
    $this->source = md5(time());
  }

  /**
   * Test recipients.
   */
  public function testRecipients() {

    // Test recipients on construct
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $this->assertEquals($this->recipients, $class->getRecipients());

    // Test recipients set directly
    $new_recipients = array('user5', 'user6');
    $class->setRecipients($new_recipients);
    $this->assertEquals($new_recipients, $class->getRecipients());

    // Test recipient appending
    $new_recipient = 'user7';
    $class->appendRecipient($new_recipient);
    $this->assertEquals(array('user5', 'user6', 'user7'), $class->getRecipients());
  }

  /**
   * Test recipient InvalidArgumentException.
   */
  public function testRecipientsInvalidArgumentException() {
    $this->setExpectedException(InvalidArgumentException::class, 'Recipient argument must be string.');
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $class->appendRecipient(TRUE);
  }

  /**
   * Test that source type gets checked.
   */
  public function testSourceMustBeString() {
    $this->setExpectedException(InvalidArgumentException::class, 'Source must be an string.');
    new MECEServiceMessage($this->recipients, TRUE);
  }

  /**
   * Test that source gets set.
   */
  public function testSource() {

    // Test that source gets set from constructor
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $this->assertEquals($this->source, $class->getSource());

    // Test that it can be set also directly
    $new_source = md5(time()-rand(1,100));
    $class->setSource($new_source);
    $this->assertEquals($new_source, $class->getSource());

    // Test that when trying to set non-string value, we get exception
    $this->setExpectedException(InvalidArgumentException::class, "Given value type 'boolean' for 'source' property is not a string.");
    $class->setSource(TRUE);

  }

}
