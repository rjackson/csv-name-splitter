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
    // Full names, with an initialed or full first name
    "/(?<title>\w+)\.?\s+?(?<firstName>\w+)\.?\s+?(?<lastName>\w+)/",
  ];

  /**
   * Parse free-text input and try to extract a Person record from it.
   *
   * At present, this only supports Western name formats.
   */
  function parse(string $input): ?Person
  {
    foreach ($this->patterns as $pattern) {
      preg_match($pattern, $input, $matches);
      if (empty($matches)) {
        continue;
      }

      ["title" => $title, "firstName" => $firstName, "lastName" => $lastName] = $matches;

      // Derive initial from first name
      $initial = $firstName[0] ?? null;
      if (mb_strlen($firstName) == 1) {
        $firstName = null;
      }

      return new Person($title, $firstName, $initial, $lastName);
    }

    return null;
  }
}
