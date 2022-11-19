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
    $person = $this->parseSingle($input);

    if (!$person instanceof Person) {
      return null;
    }

    return [$person];
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
}
