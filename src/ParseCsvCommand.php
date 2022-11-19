<?php

namespace Rjackson\CsvNameParser;

use Rjackson\CsvNameParser\Data\Person;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCsvCommand extends Command
{
  const FORMAT_TEXT = "text";
  const FORMAT_JSON = "json";

  /**
   * @inheritDoc
   */
  protected function configure()
  {
    $this->setName("parse-csv");
    $this->setDescription("Parse a CSV file and return resulting names");
    $this->addArgument("filename", InputArgument::REQUIRED, "Path to the CSV file.");
    $this->addOption("format", null, InputOption::VALUE_OPTIONAL, "Output format. text or json", "text");
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $filename = $input->getArgument("filename");
    $format = $input->getOption("format");

    if (!in_array($format, [self::FORMAT_TEXT, self::FORMAT_JSON])) {
      throw new InvalidOptionException("Unsupported format: $format");
    }

    $parser = new Parser();
    $persons = $parser->parseCsv($filename);

    switch ($format) {
      case self::FORMAT_TEXT:
        $this->renderTable($persons, $output);
        break;

      case self::FORMAT_JSON:
        $this->renderJson($persons, $output);
        break;
    }

    return self::SUCCESS;
  }

  /**
   * Render a list of Person records as a table
   *
   * @param Person[] $persons
   * @param OutputInterface $output
   * @return void
   */
  protected function renderTable(array $persons, OutputInterface $output): void
  {
    $table = new Table($output);
    $table
      ->setHeaders(["Title", "First name", "Initial", "Last name"])
      ->setRows(
        array_map(
          fn(Person $person) => [$person->title, $person->firstName, $person->initial, $person->lastName],
          $persons
        )
      );

    $table->render();
  }

  /**
   * Render a list of Person records as a json
   *
   * @param Person[] $persons
   * @param OutputInterface $output
   * @return void
   */
  protected function renderJson(array $persons, OutputInterface $output): void
  {
    $output->write(json_encode($persons, JSON_PRETTY_PRINT));
  }
}
