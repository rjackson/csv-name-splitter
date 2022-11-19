<?php

namespace Rjackson\CsvNameParser\Data;

class Person
{
  protected string $title;
  protected ?string $firstName;
  protected ?string $initial;
  protected string $lastName;

  public function __construct(string $title, ?string $firstName, ?string $initial, string $lastName)
  {
    $this->title = $title;
    $this->firstName = $firstName;
    $this->initial = $initial;
    $this->lastName = $lastName;
  }
}
