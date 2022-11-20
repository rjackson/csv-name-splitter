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
   * @covers Parser::parseSingle
   * @dataProvider parseSingleDataProvider
   */
  public function testParseSingle_Valid(string $input, Person $expectedPerson): void
  {
    $person = $this->parser->parseSingle($input);
    $this->assertInstanceOf(Person::class, $person);
    $this->assertEquals($expectedPerson, $person);
  }

  /**
   * @covers Parser::parse
   * @dataProvider parseDataProvider
   * @param string $input
   * @param Person[] $expectedPersons
   */
  public function testParse_Valid(string $input, array $expectedPersons): void
  {
    $persons = $this->parser->parse($input);
    $this->assertCount(count($expectedPersons), $persons);

    foreach ($expectedPersons as $i => $expectedPerson) {
      $this->assertInstanceOf(Person::class, $persons[$i]);
      $this->assertEquals($expectedPerson, $persons[$i]);
    }
  }

  /**
   * @covers Parser::parse
   * @dataProvider invalidDataProvider
   */
  public function testParse_Invalid($input): void
  {
    $person = $this->parser->parse($input);
    $this->assertNull($person);
  }

  /**
   * @covers Parser::parseCsv
   */
  public function testParseCsv(): void
  {
    $persons = $this->parser->parseCsv(__DIR__ . "/fixtures/empty.csv");
    $this->assertEmpty($persons);

    $persons = $this->parser->parseCsv(__DIR__ . "/fixtures/example.csv");
    $this->assertCount(20, $persons);

    // Some hardcoded spot checks
    $this->assertEquals($persons[0], new Person("Mr", "John", "J", "Smith"));
    $this->assertEquals($persons[5], new Person("Mrs", null, null, "Smith"));
    $this->assertEquals($persons[10], new Person("Mr", "John", "J", "Doe"));
    $this->assertEquals($persons[17], new Person("Mr", null, "F", "Fredrickson"));
  }

  public function parseSingleDataProvider(): array
  {
    return [
      "Fully formed" => ["Mr John Smith", new Person("Mr", "John", "J", "Smith")],
      "Initial, variation 1" => ["Mrs J. Smith", new Person("Mrs", null, "J", "Smith")],
      "Initial, variation 2" => ["Mx J Smith", new Person("Mx", null, "J", "Smith")],
      "Title, variation 1" => ["Mrs. Jennifer Smith", new Person("Mrs", "Jennifer", "J", "Smith")],
      "Title, variation 2" => ["Dr Julie Smith", new Person("Dr", "Julie", "J", "Smith")],
      "Required fields only" => ["Mr Smith", new Person("Mr", null, null, "Smith")],
      "Compound surnames, variation 1 (dash)" => ["Mr Smith-Jones", new Person("Mr", null, null, "Smith-Jones")],
      "Compound surnames, variation 2 (endash)" => ["Mr Smith–Jones", new Person("Mr", null, null, "Smith–Jones")],
      "Compound surnames, variation 3 (emdash)" => ["Mr Smith—Jones", new Person("Mr", null, null, "Smith—Jones")],
      "Ignore middle names, variation 1" => ["Mr John Edward Smith", new Person("Mr", "John", "J", "Smith")],
      "Ignore middle names, variation 2" => ["Mr John E Smith", new Person("Mr", "John", "J", "Smith")],
      "Ignore middle names, variation 3" => ["Mr John E. Smith", new Person("Mr", "John", "J", "Smith")],
      "Diacritics" => ["Chef Jacques Pépin", new Person("Chef", "Jacques", "J", "Pépin")],
      "Punctuated names" => ["Mr Chris O'Dowd", new Person("Mr", "Chris", "C", "O'Dowd")],
    ];
  }

  public function parseDataProvider(): array
  {
    return [
      // Maintain compatibility with all single names
      ...array_map(fn($entry) => [$entry[0], [$entry[1]]], $this->parseSingleDataProvider()),
      "Separate names" => [
        "Mr Bob Jones & Mrs Bobbie Smith",
        [new Person("Mr", "Bob", "B", "Jones"), new Person("Mrs", "Bobbie", "B", "Smith")],
      ],
      "Separate titles, but same first names?" => [
        "Mr and Mrs Alex Smith",
        [new Person("Mr", "Alex", "A", "Smith"), new Person("Mrs", "Alex", "A", "Smith")],
      ],
      "Separate titles, surnames only" => [
        "Prof and Dr. Wood",
        [new Person("Prof", null, null, "Wood"), new Person("Dr", null, null, "Wood")],
      ],
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
