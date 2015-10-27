# phpunit-fasttrap

FastTrap reports on fast-running tests in your PHPUnit test suite right in your console.

Typical usage of it - you need to separate integration tests from unit tests mixed in together. Main benefit of unit
test is his speed, so you can detect most of the unit tests between integration by adding this listener.

## Installation

FastTrap is installable via [Composer](http://getcomposer.org) and should be added as a `require-dev` dependency:

    composer require --dev sergekukharev/phpunit-fasttrap


## Usage

Enable with all defaults by adding the following to your test suite's `phpunit.xml` file:

```xml
<phpunit bootstrap="vendor/autoload.php">
...
    <listeners>
        <listener class="SergeKukharev\PHPUnit\Listener\FastTrapListener" />
    </listeners>
</phpunit>
```

Now run your test suite as normal. If tests run that do not exceed threshold (500ms by default), FastTrap will report on them in the console after the suite completes.

## Configuration

FastTrap has two configurable parameters:

* **fastThreshold** - Number of milliseconds a test takes to execute before being considered "fast" (Default: 500ms)
* **reportLength** - Number of fast tests included in the report (Default: 10 tests)

These configuration parameters are set in `phpunit.xml` when adding the listener:

```xml
<phpunit bootstrap="vendor/autoload.php">
    <!-- ... other suite configuration here ... -->

    <listeners>
        <listener class="SergeKukharev\PHPUnit\Listener\FastTrapListener">
            <arguments>
                <array>
                    <element key="fastThreshold">
                        <integer>500</integer>
                    </element>
                    <element key="reportLength">
                        <integer>100</integer>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
```

This allows you to set your own criteria for "fast" tests, and how many you care to know about.

## Custom fast threshold per-test method

You may have a few tests in your suite that take a little bit longer to run, and want to have a higher fast threshold than the rest of your suite.

You can use the annotation `@fastThreshold` to set a custom fast threshold on a per-test method basis. This number can be higher or lower than the default threshold and will be used in place of the default threshold for that specific test.

```php
class SomeTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @fastThreshold 5000
     */
    public function testLongRunningProcess()
    {
        // Code to exercise your long-running SUT
    }
}
```

## Inspiration
This project was inspired by and forked from John Kary's SpeedTrap. There is only one differences between those two - 
Fast trap is able to detect fast tests.

SpeedTrap project itself was inspired by Rspec's `-p` option that displays feedback about fast tests.

## License

phpunit-fasttrap is available under the MIT License.
