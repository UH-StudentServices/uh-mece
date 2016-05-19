<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

/**
 * Class MECEMultilingualStringValue
 *
 * Provides an class for containing multilingual strings.
 */
class MECEMultilingualStringValue {

  /**
   * @var array
   */
  private $supportedLanguages = array();

  /**
   * @var array
   */
  private $values = array();

  /**
   * Constructor of MECEMultilingualStringValue class. You may optionally pass
   * options for the class.
   *
   * @param array $options
   *   'supportedLanguages': List of supported langauges. If not given, then
   *                         constructor will set default values for you.
   */
  public function __construct(array $options = array()) {

    // Set supported languages
    if (!isset($options['supportedLanguages'])) {
      $options['supportedLanguages'] = array('fi', 'en', 'sv');
    }
    $this->setSupportedLanguages($options['supportedLanguages']);
  }

  /**
   * Setter callback for setting a value for specific language.
   * @param $value
   * @param $language
   * @return void
   */
  public function setValue($value, $language) {

    // Ensure that language is a string and that's supported language
    if (!is_string($language)) {
      throw new InvalidArgumentException('Language must be an string type.');
    }
    if (in_array($language, $this->getSupportedLanguages())) {
      throw new InvalidArgumentException('Language "' . $language . '" is not supported.');
    }

    // Ensure that value is a string
    if (!is_string($value)) {
      throw new InvalidArgumentException('Value must be an string type.');
    }

    // Set the value to given language
    $values = $this->getValues();
    $values[$language] = $value;
    $this->setValues($values);
  }

  /**
   * Getter callback for given language.
   * @param $language
   * @return string|null
   *   Returns an string of given language when available. Returns NULL if value
   *   is not set for given language.
   */
  public function getValue($language) {
    // Ensure that language is a string and that's supported language
    if (!is_string($language)) {
      throw new InvalidArgumentException('Language must be an string type.');
    }
    if (in_array($language, $this->getSupportedLanguages())) {
      throw new InvalidArgumentException('Language "' . $language . '" is not supported.');
    }

    $values = $this->getValues();
    return isset($values[$language]) ? $values[$language] : NULL;
  }

  /**
   * @param array $supportedLanguages
   */
  public function setSupportedLanguages(array $supportedLanguages) {
    $this->supportedLanguages = $supportedLanguages;
  }

  /**
   * @return array
   */
  public function getSupportedLanguages() {
    return $this->supportedLanguages;
  }

  /**
   * @param array $values
   */
  public function setValues(array $values) {
    $this->values = $values;
  }

  /**
   * @return array
   */
  public function getValues() {
    return $this->values;
  }
}
