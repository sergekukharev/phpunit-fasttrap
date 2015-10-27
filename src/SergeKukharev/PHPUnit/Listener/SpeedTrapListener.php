<?php

namespace SergeKukharev\PHPUnit\Listener;

/**
 * A PHPUnit TestListener that exposes your fastest running tests by outputting
 * results directly to the console.
 */
class FastTrapListener implements \PHPUnit_Framework_TestListener
{
    /**
     * Internal tracking for test suites.
     *
     * Increments as more suites are run, then decremented as they finish. All
     * suites have been run when returns to 0.
     *
     * @var integer
     */
    protected $suites = 0;

    /**
     * Time in milliseconds at which a test will be considered "fast" and be
     * reported by this listener.
     *
     * @var int
     */
    protected $fastThreshold;

    /**
     * Number of tests to report on for fastness.
     *
     * @var int
     */
    protected $reportLength;

    /**
     * Collection of fast tests.
     *
     * @var array
     */
    protected $fast = array();

    /**
     * Construct a new instance.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
    }

    /**
     * An error occurred.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     */
    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * A failure occurred.
     *
     * @param \PHPUnit_Framework_Test                 $test
     * @param \PHPUnit_Framework_AssertionFailedError $e
     * @param float                                   $time
     */
    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * Incomplete test.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     */
    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * Risky test.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * Skipped test.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param \Exception              $e
     * @param float                   $time
     */
    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }

    /**
     * A test started.
     *
     * @param \PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
    }

    /**
     * A test ended.
     *
     * @param \PHPUnit_Framework_Test $test
     * @param float                   $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase) return;

        $time = $this->toMilliseconds($time);
        $threshold = $this->getFastThreshold($test);

        if ($this->isFast($time, $threshold)) {
            $this->addFastTest($test, $time);
        }
    }

    /**
     * A test suite started.
     *
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites++;
    }

    /**
     * A test suite ended.
     *
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites--;

        if (0 === $this->suites && $this->hasFastTests()) {
            arsort($this->fast); // Sort longest running tests to the top

            $this->renderHeader();
            $this->renderBody();
            $this->renderFooter();
        }
    }

    /**
     * Whether the given test execution time is considered fast.
     *
     * @param int $time          Test execution time in milliseconds
     * @param int $fastThreshold Test execution time at which a test should be considered fast (milliseconds)
     * @return bool
     */
    protected function isFast($time, $fastThreshold)
    {
        return $time <= $fastThreshold;
    }

    /**
     * Stores a test as fast.
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @param int                         $time Test execution time in milliseconds
     */
    protected function addFastTest(\PHPUnit_Framework_TestCase $test, $time)
    {
        $label = $this->makeLabel($test);

        $this->fast[$label] = $time;
    }

    /**
     * Whether at least one test has been considered fast.
     *
     * @return bool
     */
    protected function hasFastTests()
    {
        return !empty($this->fast);
    }

    /**
     * Convert PHPUnit's reported test time (microseconds) to milliseconds.
     *
     * @param float $time
     * @return int
     */
    protected function toMilliseconds($time)
    {
        return (int) round($time * 1000);
    }

    /**
     * Label for describing a test.
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @return string
     */
    protected function makeLabel(\PHPUnit_Framework_TestCase $test)
    {
        return sprintf('%s:%s', get_class($test), $test->getName());
    }

    /**
     * Calculate number of fast tests to report about.
     *
     * @return int
     */
    protected function getReportLength()
    {
        return min(count($this->fast), $this->reportLength);
    }

    /**
     * Find how many fast tests occurred that won't be shown due to list length.
     *
     * @return int Number of hidden fast tests
     */
    protected function getHiddenCount()
    {
        $total = count($this->fast);
        $showing = $this->getReportLength();

        $hidden = 0;
        if ($total > $showing) {
            $hidden = $total - $showing;
        }

        return $hidden;
    }

    /**
     * Renders fast test report header.
     */
    protected function renderHeader()
    {
        echo sprintf("\n\nThese tests are fast enough: (<%sms)...\n", $this->fastThreshold);
    }

    /**
     * Renders fast test report body.
     */
    protected function renderBody()
    {
        $fastTests = $this->fast;

        $length = $this->getReportLength();
        for ($i = 1; $i <= $length; ++$i) {
            $label = key($fastTests);
            $time = array_shift($fastTests);

            echo sprintf(" %s. %sms to run %s\n", $i, $time, $label);
        }
    }

    /**
     * Renders fast test report footer.
     */
    protected function renderFooter()
    {
        if ($hidden = $this->getHiddenCount()) {
            echo sprintf("...and there %s %s more above your threshold hidden from view", $hidden == 1 ? 'is' : 'are', $hidden);
        }
    }

    /**
     * Populate options into class internals.
     *
     * @param array $options
     */
    protected function loadOptions(array $options)
    {
        $this->fastThreshold = isset($options['fastThreshold']) ? $options['fastThreshold'] : 500;
        $this->reportLength = isset($options['reportLength']) ? $options['reportLength'] : 10;
    }

    /**
     * Get fast test threshold for given test. A TestCase can override the
     * suite-wide fast threshold by using the annotation @fastThreshold with
     * the threshold value in milliseconds.
     *
     * The following test will only be considered fast when its execution time
     * reaches 5000ms (5 seconds):
     *
     * <code>
     * \@fastThreshold 5000
     * public function testLongRunningProcess() {}
     * </code>
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @return int
     */
    protected function getFastThreshold(\PHPUnit_Framework_TestCase $test)
    {
        $ann = $test->getAnnotations();

        return isset($ann['method']['fastThreshold'][0]) ? $ann['method']['fastThreshold'][0] : $this->fastThreshold;
    }
}
