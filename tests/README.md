# quiqqer/redirect Unit-Tests

Unit tests are powered by [PHPUnit](https://phpunit.de/).

## Setup PHPUnit

PHPUnit has to be installed globally on your machine or locally in your QUIQQER system.  
Follow [this guide](https://phpunit.de/getting-started/phpunit-9.html) on how to install PHPUnit.

## Test Execution

To run the unit tests, execute the following command from your QUIQQER-system's root directory (assuming PHPUnit is installed globally):

```shell
phpunit --bootstrap packages/quiqqer/redirect/tests/bootstrap.php packages/quiqqer/redirect/tests/
```

_If you have PHPUnit installed locally replace `phpunit` in the command above with the appropriate path to your PHPUnit executable (e.g. `php packages/phpunit/phpunit/phpunit`)._
