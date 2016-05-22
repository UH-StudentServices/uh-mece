<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

require_once __DIR__ . '/../src/MECEMultilingualStringValue.php';
require_once __DIR__ . '/../src/MECEServiceMessage.php';

/**
 * Class MECEServiceMessageTest
 *
 * @coversDefaultClass \MECEServiceMessage
 */
class MECEServiceMessageTest extends PHPUnit_Framework_TestCase {

  private $recipients = array('user1', 'user2', 'user3', 'user4');
  private $source;

  public function setUp() {
    $this->source = $this->getRandomString();
  }

  private function getRandomString() {
    return md5(uniqid(time(), TRUE));
  }

  /**
   * Test recipients.
   * @covers ::setRecipients
   * @covers ::getRecipients
   * @covers ::appendRecipients
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
   * @covers ::appendRecipient
   */
  public function testRecipientsInvalidArgumentException() {
    $this->setExpectedException(InvalidArgumentException::class, 'Recipient argument must be string.');
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $class->appendRecipient(TRUE);
  }

  /**
   * Test that source type gets checked.
   * @covers ::__construct
   */
  public function testSourceMustBeString() {
    $this->setExpectedException(InvalidArgumentException::class, 'Source must be an string.');
    new MECEServiceMessage($this->recipients, TRUE);
  }

  /**
   * Test that source gets set.
   * @covers ::getSource
   * @covers ::setSource
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

  /**
   * Test that priority accepts only string type
   * @covers ::__construct
   * @covers ::setPriority
   */
  public function testPriorityException() {
    $this->setExpectedException(InvalidArgumentException::class, "Given value type 'boolean' for 'priority' property is not a string.");
    new MECEServiceMessage($this->recipients, $this->source, array('priority' => TRUE));
  }

  /**
   * Test priority.
   * @covers ::__construct
   * @covers ::setPriority
   * @covers ::getPriority
   */
  public function testPriority() {
    $priority = $this->getRandomString();

    // Test that priority gets set through constructor
    $class = new MECEServiceMessage($this->recipients, $this->source, array('priority' => $priority));
    $this->assertEquals($priority, $class->getPriority());

    // Test that priority gets set and get properly
    $default_priority = '1';
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $this->assertEquals($default_priority, $class->getPriority());
    $class->setPriority($priority);
    $this->assertEquals($priority, $class->getPriority());
  }

  /**
   * Test default datetime values.
   * @covers ::__construct
   * @covers ::getExpiration
   * @covers ::getDeadline
   * @covers ::getSubmitted
   */
  public function testDefaultDateTimeValues() {
    $defaultValue = new DateTime('now', new DateTimeZone('Etc/Zulu'));
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $this->assertEquals($defaultValue, $class->getExpiration());
    $this->assertEquals($defaultValue, $class->getDeadline());
    $this->assertEquals($defaultValue, $class->getSubmitted());
  }

  /**
   * Test setting and getting datetime values.
   * @covers ::setExpiration
   * @covers ::getExpiration
   * @covers ::setDeadline
   * @covers ::getDeadline
   * @covers ::setSubmitted
   * @covers ::getSubmitted
   */
  public function testSetGetDateProperties() {
    $class = new MECEServiceMessage($this->recipients, $this->source);

    // Test setting expiration
    $newValue = new DateTime('+5 day', new DateTimeZone('Etc/Zulu'));
    $class->setExpiration($newValue);
    $this->assertEquals($newValue, $class->getExpiration());

    // Test setting deadline
    $newValue = new DateTime('+3 day', new DateTimeZone('Etc/Zulu'));
    $class->setDeadline($newValue);
    $this->assertEquals($newValue, $class->getDeadline());

    // Test setting submitted
    $newValue = new DateTime('+1 day', new DateTimeZone('Etc/Zulu'));
    $class->setSubmitted($newValue);
    $this->assertEquals($newValue, $class->getSubmitted());
  }

  /**
   * Expiration should not be able to set before submitted.
   * @covers ::setExpiration
   */
  public function testInvalidExpirationDateTimeBeforeSubmitted() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newInvalidValue = new DateTime('-1 day', new DateTimeZone('Etc/Zulu'));
    $this->setExpectedException(LogicException::class, 'Expiration can not be before submitted.');
    $class->setExpiration($newInvalidValue);
  }

  /**
   * Expiration should not be able to set before deadline.
   * @covers ::setExpiration
   * @covers ::setDeadline
   */
  public function testInvalidExpirationDateTimeBeforeDeadline() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $class->setExpiration(new DateTime('+3 day', new DateTimeZone('Etc/Zulu')));
    $class->setDeadline(new DateTime('+2 day', new DateTimeZone('Etc/Zulu')));

    $newInvalidValue = new DateTime('+1 day', new DateTimeZone('Etc/Zulu'));
    $this->setExpectedException(LogicException::class, 'Expiration can not be before deadline.');
    $class->setExpiration($newInvalidValue);
  }

  /**
   * Submitted should not be able to be set after expiration.
   * @covers ::setSubmitted
   */
  public function testInvalidSubmittedDateTimeAfterExpiration() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newInvalidValue = new DateTime('+1 day', new DateTimeZone('Etc/Zulu'));
    $this->setExpectedException(LogicException::class, 'Submitted can not be after expiration.');
    $class->setSubmitted($newInvalidValue);
  }

  /**
   * Deadline should not be able to be set after expiration.
   * @covers ::setDeadline
   */
  public function testInvalidDeadlineDateTimeAfterExpiration() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newInvalidValue = new DateTime('+1 day', new DateTimeZone('Etc/Zulu'));
    $this->setExpectedException(LogicException::class, 'Deadline can not be after expiration.');
    $class->setDeadline($newInvalidValue);
  }

  /**
   * Deadline should be able to be set before submitted.
   * @covers ::setDeadline
   * @covers ::getDeadline
   */
  public function testValidDeadlineDateTimeBeforeSubmitted() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = new DateTime('-1 day', new DateTimeZone('Etc/Zulu'));
    $class->setDeadline($newValue);
    $this->assertEquals($newValue, $class->getDeadline());
  }

  /**
   * Text property SourceId should be able to set and get the value.
   * @covers ::setSourceId
   * @covers ::getSourceId
   */
  public function testSetGetSourceId() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = $this->getRandomString();
    $class->setSourceId($newValue);
    $this->assertEquals($newValue, $class->getSourceId());
  }

  /**
   * SourceId should be always an string.
   * @covers ::setSourceId
   */
  public function testInvalidSourceId() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $this->setExpectedException(InvalidArgumentException::class, "Given value type 'boolean' for 'sourceId' property is not a string.");
    $class->setSourceId(TRUE);
  }

  /**
   * Multilingual string property heading should be able to set and get.
   * @covers ::setHeading
   * @covers ::getHeading
   */
  public function testSetGetHeading() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = new MECEMultilingualStringValue();
    $newValue->setValue($this->getRandomString(), 'fi');
    $newValue->setValue($this->getRandomString(), 'en');
    $newValue->setValue($this->getRandomString(), 'sv');
    $class->setHeading($newValue);
    $this->assertEquals($newValue, $class->getHeading());
  }

  /**
   * Multilingual string property linkText should be able to set and get.
   * @covers ::setLinkText
   * @covers ::getLinkText
   */
  public function testSetGetLinkText() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = new MECEMultilingualStringValue();
    $newValue->setValue($this->getRandomString(), 'fi');
    $newValue->setValue($this->getRandomString(), 'en');
    $newValue->setValue($this->getRandomString(), 'sv');
    $class->setLinkText($newValue);
    $this->assertEquals($newValue, $class->getLinkText());
  }

  /**
   * Multilingual string property link should be able to set and get.
   * @covers ::setLink
   * @covers ::getLink
   */
  public function testSetGetLink() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = new MECEMultilingualStringValue();
    $newValue->setValue($this->getRandomString(), 'fi');
    $newValue->setValue($this->getRandomString(), 'en');
    $newValue->setValue($this->getRandomString(), 'sv');
    $class->setLink($newValue);
    $this->assertEquals($newValue, $class->getLink());
  }

  /**
   * Multilingual string property message should be able to set and get.
   * @covers ::setMessage
   * @covers ::getMessage
   */
  public function testSetGetMessage() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = new MECEMultilingualStringValue();
    $newValue->setValue($this->getRandomString(), 'fi');
    $newValue->setValue($this->getRandomString(), 'en');
    $newValue->setValue($this->getRandomString(), 'sv');
    $class->setMessage($newValue);
    $this->assertEquals($newValue, $class->getMessage());
  }

  /**
   * Text property avatarImageUrl should be able to set and get the value.
   * @covers ::setAvatarImageUrl
   * @covers ::getAvatarImageUrl
   */
  public function testSetGetAvatarImageUrl() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $newValue = $this->getRandomString();
    $class->setAvatarImageUrl($newValue);
    $this->assertEquals($newValue, $class->getAvatarImageUrl());
  }

  /**
   * AvatarImageUrl should be always an string.
   * @covers ::setAvatarImageUrl
   */
  public function testInvalidAvatarImageUrl() {
    $class = new MECEServiceMessage($this->recipients, $this->source);
    $this->setExpectedException(InvalidArgumentException::class, "Given value type 'boolean' for 'avatarImageUrl' property is not a string.");
    $class->setAvatarImageUrl(TRUE);
  }

}
