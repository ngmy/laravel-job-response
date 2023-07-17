# Changelog

All notable changes to `laravel-job-response` will be documented in this file

## 0.2.0 - 0.3.0 - 2023-06-27

- Fix based on PHPStan errors.
- Switch the required Redis client from Predis to PhpRedis.

## 0.1.3 - 0.2.0 - 2023-06-19

- Add compatibility with Laravel 9 and 10. Drop compatibility with Laravel 7. Require PHP 8.1 or higher.

## 0.1.2 - 0.1.3 - 2020-06-11

- Allow jobs to be manually failed. Laravel Jobs can be manually failed with `$job->fail()` with an optional
\Throwable - this release provides the transport capabilities for the response to handle this use case and the
corresponding tests.

## 0.1.1 - 0.1.2 - 2020-06-11

- Bug fixes and Travis CI setup.
- Altered functionality of Exception handling. Initially this serialized the exceptions and attached them unserialized
however this was not reliable and broke functionality with Monolog/casting exceptions to strings.

## 0.1.0 - 2020-06-11

- Initial release
