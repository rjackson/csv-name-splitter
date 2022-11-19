<?php

namespace Rjackson\CsvNameParser\Data;

use JsonSerializable;

class Person implements JsonSerializable
{
  public readonly string $title;
  public readonly ?string $firstName;
  public readonly ?string $initial;
  public readonly string $lastName;

  public function __construct(string $title, ?string $firstName, ?string $initial, string $lastName)
  {
    $this->title = $title;
    $this->firstName = $firstName;
    $this->initial = $initial;
    $this->lastName = $lastName;
  }

  public function jsonSerialize(): mixed
  {
    return [
      "title" => $this->title,
      "first_name" => $this->firstName,
      "initial" => $this->initial,
      "last_name" => $this->lastName,
    ];
  }
}
