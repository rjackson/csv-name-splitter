<?php

use PHPUnit\Framework\TestCase;
use Rjackson\CsvNameParser\ParseCsvCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class ParseCsvCommandTest extends TestCase
{
  /** @var CommandTester */
  private $commandTester;

  protected function setUp(): void
  {
    $application = new Application();
    $application->add(new ParseCsvCommand());
    $command = $application->find("parse-csv");
    $this->commandTester = new CommandTester($command);
  }

  /**
   * @covers ParseCsvCommand
   * @dataProvider executeDataProvider
   */
  public function testExecute($format, $filename, $expectedFile)
  {
    $this->commandTester->execute(["filename" => $filename, "--format" => $format]);

    $this->assertEquals(0, $this->commandTester->getStatusCode());
    $this->assertStringEqualsFile($expectedFile, $this->commandTester->getDisplay());
  }

  /**
   * @covers ParseCsvCommand
   * @dataProvider invalidArgumentsDataProvider
   */
  public function testInvalidArguments($arguments, $expectedException)
  {
    $this->expectException($expectedException);
    $this->commandTester->execute($arguments);
  }

  public function executeDataProvider(): array
  {
    return [
      ["json", __DIR__ . "/fixtures/example.csv", __DIR__ . "/fixtures/example.json"],
      ["text", __DIR__ . "/fixtures/example.csv", __DIR__ . "/fixtures/example.txt"],
    ];
  }

  public function invalidArgumentsDataProvider(): array
  {
    return [
      [[], RuntimeException::class],
      [["--format" => "yaml"], RuntimeException::class],
      [["--blah" => "blop"], InvalidOptionException::class],
    ];
  }
}
