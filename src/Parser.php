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
   * Patterns against which to match names, from most to least specific
   *
   * We could try to write a mega-regex to do this, but mulitple simpler patterns
   * will be easier to maintain.
   *
   * @var string[]
   */
  protected array $patterns = [
    // Single name where
    // - title is mandatory
    // - firstName is optional, and either a full word or initialied
    // - middleName is optional, and either a full word or initialied
    // - lastName is mandatory
    "/^(?<title>\w+)\.?\s+?((?<firstName>\w+)\.?\s+)?((?<middleName>\w+)\.?\s+)?(?<lastName>[\w\-–—]+)$/",
  ];

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
    foreach ($this->patterns as $pattern) {
      preg_match($pattern, $input, $matches);
      if (empty($matches)) {
        continue;
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

    return null;
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
