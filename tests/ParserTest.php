<?php

use PHPUnit\Framework\TestCase;
use Rjackson\CsvNameParser\Data\Person;
use Rjackson\CsvNameParser\Parser;

class ParserTest extends TestCase
{
  protected Parser $parser;

  public function setUp(): void
  {
    $this->parser = new Parser();
  }

  /**
   * @covers Parser::parse
   * @dataProvider validDataProvider_Singles
   */
  public function testParse_Singles_Valid($input, Person $expectedPerson): void
  {
    $person = $this->parser->parse($input);
    $this->assertInstanceOf(Person::class, $person);
    $this->assertEquals($expectedPerson, $person);
  }

  /**
   * @covers Parser::parse
   * @dataProvider invalidDataProvider
   */
  public function testParse_Singles_Invalid($input): void
  {
    $person = $this->parser->parse($input);
    $this->assertNull($person);
  }

  public function validDataProvider_Singles(): array
  {
    return [
      "Fully formed" => ["Mr John Smith", new Person("Mr", "John", "J", "Smith")],
      "Initial, variation 1" => ["Mrs J. Smith", new Person("Mrs", null, "J", "Smith")],
      "Initial, variation 2" => ["Mx J Smith", new Person("Mx", null, "J", "Smith")],
      // "Title, variation 1" => ["Mrs. Jennifer Smith", new Person("Mrs", "Jennifer", "J", "Smith")],
      // "Title, variation 2" => ["Dr Julie Smith", new Person("Dr", "Julie", "J", "Smith")],
      //   "Required fields only" => ["Mr Smith", new Person("Mr", null, "J", "Smith")],
    ];
  }

  public function invalidDataProvider(): array
  {
    return [
      "Blank" => [""],
      "Mononym" => ["Prince"],
    ];
  }
}
