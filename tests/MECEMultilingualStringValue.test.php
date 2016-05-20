<?php
/**
 * @license GPL, or GNU General Public License, version 3
 * @license http://opensource.org/licenses/GPL-3.0
 * @see README.md how to contribute to this project
 */

require_once __DIR__ . '/../src/MECEMultilingualStringValue.php';

class MECEMultilingualStringValueTest extends PHPUnit_Framework_TestCase {

  /**
   * @var MECEMultilingualStringValue
   */
  private $class;

  public function setUp() {
    $this->class = new MECEMultilingualStringValue();
  }

  public function testConstruct() {

    // Assert default supported languages
    $class = new MECEMultilingualStringValue();
    $this->assertArraySubset(array('fi', 'en', 'sv'), $class->getSupportedLanguages());

    // Assert that given supported languages are set
    $class = new MECEMultilingualStringValue(array('supportedLanguages' => array('ru')));
    $this->assertArraySubset(array('ru'), $class->getSupportedLanguages());
  }

  public function testSetValueLangaugeMustBeString() {
    $this->setExpectedException(InvalidArgumentException::class, 'Language must be an string type.');
    $this->class->setValue('value', TRUE);
  }

  public function testSetValueValueMustBeString() {
    $this->setExpectedException(InvalidArgumentException::class, 'Value must be an string type.');
    $this->class->setValue(TRUE, 'fi');
  }

  public function testSetValueNotSupportedLanguage() {
    $this->setExpectedException(InvalidArgumentException::class, 'Language "ru" is not supported.');
    $this->class->setValue('value', 'ru');
  }

  public function testValues() {

    // Test setValue() and getValue()
    $this->class->setValue('hello', 'en');
    $this->assertEquals('hello', $this->class->getValue('en'));
    $this->class->setValue('terve', 'fi');
    $this->assertEquals('terve', $this->class->getValue('fi'));

    // Test multiple values
    $this->assertArraySubset(array('en' => 'hello', 'fi' => 'terve'), $this->class->getValues());

    // Test whole set/get values
    $values = array('fi' => 'terve', 'en' => 'hello', 'sv' => 'hej');
    $this->class->setValues($values);
    $this->assertArraySubset($values, $this->class->getValues());

  }

  public function testSupportedLanguages() {
    // Test setter and getter
    $values = array('fi', 'en');
    $this->class->setSupportedLanguages($values);
    $this->assertArraySubset($values, $this->class->getSupportedLanguages());
  }

}
