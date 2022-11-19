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
   * Parse free-text input and try to extract a Person record from it.
   *
   * At present, this only supports Western name formats.
   */
  function parse(string $input): ?Person
  {
    return null;
  }
}
