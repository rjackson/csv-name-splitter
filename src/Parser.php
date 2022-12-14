<?php

namespace Rjackson\CsvNameParser;

use Rjackson\CsvNameParser\Data\Person;

/**
 * Parser converts data provided via a number of formats, and attempts to extract
 * people's names and normalise them into Person records.
 */
class Parser
{
  /**
   * Regex pattern to identify name segments, where:
   * - title is mandatory
   * - firstName is optional, and either a full word or initialied
   * - middleName is optional, and either a full word or initialied
   * - lastName is mandatory
   */
  protected string $namePattern = "/^(?<title>[^\s\.]+)\.?\s+?((?<firstName>[^\s\.]+)\.?\s+)?((?<middleName>[^\s\.]+)\.?\s+)?(?<lastName>[^\s\.]+)$/";

  /**
   * Parse a CSV of free-text names and resolve into Person records
   *
   * @param string $filename
   * @return Person[]
   */
  function parseCsv(string $filename): array
  {
    $f = fopen($filename, "r");
    if ($f === false) {
      return [];
    }

    $persons = [];
    try {
      while (($row = fgetcsv($f)) !== false) {
        [$name] = $row;
        if (!$name) {
          continue;
        }
        array_push($persons, ...$this->parse($name) ?: []);
      }
    } finally {
      fclose($f);
    }

    return $persons;
  }

  /**
   * Parse free-text input and try to extract a Person record from it.
   *
   * At present, this only supports Western name formats.
   *
   * @return Person[]|null
   */
  function parse(string $input): array|null
  {
    return array_reduce(
      $this->extractNames($input),
      function ($parsedNames, $name) {
        $person = $this->parseSingle($name);

        if ($person instanceof Person) {
          $parsedNames[] = $person;
        }

        return $parsedNames;
      },
      null
    );
  }

  /**
   * Parse free-text input which we're certain only represents a single name,
   * and try to extract a Person record from it
   *
   * At present, this only supports Western name formats.
   */
  function parseSingle(string $input): ?Person
  {
    preg_match($this->namePattern, $input, $matches);
    if (empty($matches)) {
      return null;
    }

    // middleName is deliberately not extracted because we don't need it
    ["title" => $title, "firstName" => $firstName, "lastName" => $lastName] = $matches;

    // Derive initial from first name
    $initial = $firstName[0] ?? null;
    if (mb_strlen($firstName) <= 1) {
      $firstName = null;
    }

    return new Person($title, $firstName, $initial, $lastName);
  }

  /**
   * Parse free-text input, and split it into multiple names if it contains conjunctions
   *
   * @return string[]
   */
  protected function extractNames(string $input): array
  {
    $names = [];

    // Check for any conjunction (skipping cost of regex as there aren't many to cover)
    foreach ([" and ", " & "] as $conjunction) {
      if (!str_contains($input, $conjunction)) {
        continue;
      }

      [$left, $right] = explode($conjunction, $input, 2);

      // If the left segment contains spaces, assume its a full name.
      // Kind of naive, but works for our test cases
      if (str_contains($left, " ")) {
        $names[] = $left;
        $names[] = $right;
      }

      // Otherwise assume we split on a title.
      else {
        [$right, $restOfName] = explode(" ", $right, 2);
        $names[] = sprintf("%s %s", $left, $restOfName);
        $names[] = sprintf("%s %s", $right, $restOfName);
      }
    }

    if (empty($names)) {
      $names[] = $input;
    }

    return $names;
  }
}
