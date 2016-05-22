<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

/**
 * Class MECEServiceMessage
 *
 * Provides an class for setting and dumping the message that is designed to be
 * sent to MECE Service.
 */
class MECEServiceMessage {

  /*
   * Following list of properties are part of the service message.
   */

  /**
   * @var array
   */
  private $recipients = array();

  /**
   * @var string
   */
  private $priority = '';

  /**
   * @var DateTime
   */
  private $deadline;

  /**
   * @var DateTime
   */
  private $expiration;

  /**
   * @var DateTime
   */
  private $submitted;

  /**
   * @var string
   */
  private $source = '';

  /**
   * @var string
   */
  private $sourceId = '';

  /**
   * @var MECEMultilingualStringValue
   */
  private $heading;

  /**
   * @var MECEMultilingualStringValue
   */
  private $message;

  /**
   * @var MECEMultilingualStringValue
   */
  private $linkText;

  /**
   * @var MECEMultilingualStringValue
   */
  private $link;

  /**
   * @var string
   */
  private $avatarImageUrl = '';

  /*
   * Following list of properties are for this class implementation.
   */

  /**
   * @var array
   */
  private $supportedLanguages = array();

  /**
   * @var DateTimeZone
   */
  private $requiredTimeZone;

  /**
   * Class constructor for MECEServiceMessage.
   * @param array $recipients
   * @param string $source
   * @param array $options
   *   'priority':           String value for priority. Optional (default: '1').
   *   'supportedLanguages': List of languages to support. Optional (default:
   *                         'fi', 'en', 'sv')
   */
  public function __construct(array $recipients, $source, array $options = array()) {

    // Set recipients
    $this->setRecipients($recipients);

    // Check source and set it
    if (!is_string($source)) {
      throw new InvalidArgumentException('Source must be an string.');
    }
    $this->setSource($source);

    // Priority and supported languages can be set from options.
    $options['priority'] = !empty($options['priority']) ? $options['priority'] : '1';
    $this->setPriority($options['priority']);
    $options['supportedLanguages'] = (isset($options['supportedLanguages']) && is_array($options['supportedLanguages'])) ? $options['supportedLanguages'] : array('fi', 'en', 'sv');
    $this->supportedLanguages = $options['supportedLanguages'];

    // Construct date values with required timezone
    $this->requiredTimeZone = new DateTimeZone('Etc/Zulu');
    $this->deadline = new DateTime('now', $this->requiredTimeZone);
    $this->expiration = new DateTime('now', $this->requiredTimeZone);
    $this->submitted = new DateTime('now', $this->requiredTimeZone);

  }

  /**
   * Exports the message object as JSON string.
   * @return string
   */
  public function export() {
    $properties = array(
      'recipients',
      'priority',
      'deadline',
      'expiration',
      'submitted',
      'source',
      'sourceId',
      'heading',
      'message',
      'linkText',
      'link',
      'avatarImageUrl',
    );
    $export = new StdClass();
    foreach ($properties as $property) {

      // Define getter method for property and ensure it exists
      $getterMethod = 'get' . ucfirst($property);
      if (!method_exists($this, $getterMethod)) {
        throw new LogicException('Getter method "' . $getterMethod . '" was not found.');
      }

      // Call the getter method and set the value to $export in certain way that
      // depends what type it is.
      $value = $this->$getterMethod();
      if (is_string($value) || is_array($value)) {
        $export->{$property} = $value;
      }
      elseif ($value instanceof DateTime) {
        $export->{$property} = $value->format('Y-m-d\TH:i:s\Z');
      }
      elseif ($value instanceof MECEMultilingualStringValue) {

        // Loop each supported language and set it as multilingual value. Same
        // time try to specify the language neutral value that will be set after
        // the loop.
        $languageNeutralValue = '';
        foreach ($this->supportedLanguages as $language) {

          // Set multilingual value
          $multilingualValue = $value->getValue($language);
          $multilingualProperty = $property . strtoupper($language);
          $export->{$multilingualProperty} = $multilingualValue;

          // This should get the first non-empty value.
          if (empty($languageNeutralValue)) {
            $languageNeutralValue = $multilingualValue;
          }
        }

        // Set the language neutral value too
        $export->{$property} = $languageNeutralValue;

      }
    }
    return json_encode($export);
  }

  /**
   * Sets recipients to given recipients.
   * @param array $recipients
   * @return void
   */
  public function setRecipients(array $recipients) {
    $this->recipients = $recipients;
  }

  /**
   * Appends an recipient to list of recipients.
   * @param string $recipient
   * @return void
   * @throws InvalidArgumentException
   */
  public function appendRecipient($recipient) {
    if (!is_string($recipient)) {
      throw new InvalidArgumentException('Recipient argument must be string.');
    }
    $this->recipients[] = $recipient;
  }

  /**
   * Returns list of recipients.
   * @return array
   */
  public function getRecipients() {
    return $this->recipients;
  }

  /**
   * Setter method for setting priority.
   * @param string $priority
   * @return void
   */
  public function setPriority($priority) {
    $this->setStringProperty($priority, 'priority');
  }

  /**
   * Getter method for getting priority.
   * @return string
   */
  public function getPriority() {
    return $this->priority;
  }

  /**
   * Setter method for setting deadline.
   * @param DateTime $deadline
   * @return void
   */
  public function setDeadline(DateTime $deadline) {

    // Deadline can't be after expiration
    if ($deadline->getTimestamp() > $this->getExpiration()->getTimestamp()) {
      throw new LogicException('Deadline can not be after expiration.');
    }

    $this->setDateProperty($deadline, 'deadline');
  }

  /**
   * Getter method for getting deadline.
   * @return DateTime
   */
  public function getDeadline() {
    return $this->deadline;
  }

  /**
   * Setter method for setting expiration.
   * @param DateTime $expiration
   * @return void
   */
  public function setExpiration(DateTime $expiration) {

    // Expiration can't be before submitted or deadline
    if ($expiration->getTimestamp() < $this->getSubmitted()->getTimestamp()) {
      throw new LogicException('Expiration can not be before submitted.');
    }
    if ($expiration->getTimestamp() < $this->getDeadline()->getTimestamp()) {
      throw new LogicException('Expiration can not be before deadline.');
    }

    $this->setDateProperty($expiration, 'expiration');
  }

  /**
   * Getter method for getting expiration.
   * @return DateTime
   */
  public function getExpiration() {
    return $this->expiration;
  }

  /**
   * Setter method for setting submitted.
   * @param DateTime $submitted
   * @return void
   */
  public function setSubmitted(DateTime $submitted) {

    // Submitted can't be after expiration
    if ($submitted->getTimestamp() > $this->getExpiration()->getTimestamp()) {
      throw new LogicException('Submitted can not be after expiration.');
    }

    $this->setDateProperty($submitted, 'submitted');
  }

  /**
   * Getter method for getting submitted.
   * @return mixed
   */
  public function getSubmitted() {
    return $this->submitted;
  }

  /**
   * Setter method for setting source.
   * @param string $source
   * @return void
   */
  public function setSource($source) {
    $this->setStringProperty($source, 'source');
  }

  /**
   * Getter method for getting source.
   * @return string
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * Setter method for setting source id.
   * @param string $sourceId
   * @return void
   */
  public function setSourceId($sourceId) {
    $this->setStringProperty($sourceId, 'sourceId');
  }

  /**
   * Getter method for getting source id.
   * @return string
   */
  public function getSourceId() {
    return $this->sourceId;
  }

  /**
   * Setter method for setting heading.
   * @param MECEMultilingualStringValue $heading
   * @return void
   */
  public function setHeading(MECEMultilingualStringValue $heading) {
    $this->heading = $heading;
  }

  /**
   * Getter method for getting heading.
   * @return MECEMultilingualStringValue
   */
  public function getHeading() {
    return $this->heading;
  }

  /**
   * Setter method for setting message.
   * @param MECEMultilingualStringValue $message
   * @return void
   */
  public function setMessage(MECEMultilingualStringValue $message) {
    $this->message = $message;
  }

  /**
   * Getter method for getting message.
   * @return MECEMultilingualStringValue
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Setter method for setting link text.
   * @param MECEMultilingualStringValue $linkText
   * @return void
   */
  public function setLinkText(MECEMultilingualStringValue $linkText) {
    $this->linkText = $linkText;
  }

  /**
   * Getter method for getting link text.
   * @return MECEMultilingualStringValue
   */
  public function getLinkText() {
    return $this->linkText;
  }

  /**
   * Setter method for setting link.
   * @param MECEMultilingualStringValue $link
   * @return void
   */
  public function setLink(MECEMultilingualStringValue $link) {
    $this->link = $link;
  }

  /**
   * Getter method for getting link.
   * @return MECEMultilingualStringValue
   */
  public function getLink() {
    return $this->link;
  }

  /**
   * Setter method for setting avatar image URL.
   * @param string $avatarImageUrl
   * @return void
   */
  public function setAvatarImageUrl($avatarImageUrl) {
    $this->setStringProperty($avatarImageUrl, 'avatarImageUrl');
  }

  /**
   * Getter method for getting avatar image URL.
   * @return string
   */
  public function getAvatarImageUrl() {
    return $this->avatarImageUrl;
  }

  /**
   * An internal private string setter method that handles type validation.
   * @param string $value
   * @param string $property
   * @throws LogicException
   * @throws InvalidArgumentException
   */
  private function setStringProperty($value, $property) {

    // Check that given $property is string
    if (!is_string($property)) {
      throw new InvalidArgumentException("Property should be type of string.");
    }

    // Check that property is found and its type of string
    if (!isset($this->{$property}) || (isset($this->{$property}) && !is_string($this->{$property}))) {
      throw new LogicException("There is no such string property as '$property'");
    }

    // Check that given string value is string
    if (!is_string($value)) {
      throw new InvalidArgumentException("Given value type '".gettype($value)."' for '$property' property is not a string.");
    }

    $this->{$property} = $value;
  }

  /**
   * An internal private method for setting date time value that validates the
   * value against required timezone.
   *
   * @param DateTime $value
   * @param $property
   */
  private function setDateProperty(DateTime $value, $property) {

    // Check that given $property is string
    if (!is_string($property)) {
      throw new InvalidArgumentException("Property should be type of string.");
    }

    // Check that property is found and its type of string
    if (!isset($this->{$property}) || (!isset($this->$property) && get_class($this->{$property}) == 'DateTime')) {
      throw new LogicException("There is no such DateTime property as '$property'");
    }

    // Check that value matches with required timezone
    if ($value->getTimezone()->getName() !== $this->requiredTimeZone->getName()) {
      throw new LogicException($property . ' DateTime value must be in timezone "' . $this->requiredTimeZone->getName() . '"');
    }

    $this->{$property} = $value;
  }

}
