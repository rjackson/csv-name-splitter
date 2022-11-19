# Name parser

This project is built to fulfil a technical challenge, rather than to be an independent project in of itself.

The challenge is to parse an arbitrary list of strings containing one or many people's incomplete names, into a normalised schema containing:

- Title (required)
- Lastname (required)
- Initial (optional)
- Forename (optional)

The sample data and schema assumes all names are provided and can be normalised into the Western name order / format.

This project, parser, and schema is unsuitable for parsing international name formats.

## Running tests

```sh
vendor/bin/phpunit
```
