<?php
/**
 * @package     WebService.Tests
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test Case class for WebServiceErrors
 *
 * @package     WebService.Tests
 * @subpackage  Application
 * @since       1.0
 */
class WebServiceErrorsTest extends TestCase
{
	/**
	 * An instance of the class to test.
	 *
	 * @var    WebServiceErrors
	 * @since  1.0
	 */
	private $_instance;

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$testInput = new JInput;
		$testMock = $this->getMockWeb();
		$this->_instance = new WebServiceErrors($testMock, $testInput);
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function tearDown()
	{
		$this->_instance = null;

		parent::tearDown();
	}

	/**
	 * Provides test data for request format detection.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedCheckSupressResponseCodesData()
	{
		// Input, Expected, Exception
		return array(
				array('', null, true),
				array(null, 200, false),
				array('true', 200, false),
				array('false', 401, false),
				array('error', null, true),
				array('TRUE', 200, false),
				array('FALSE', 401, false)
		);
	}

	/**
	 * Tests checkSupressResponseCodes()
	 *
	 * @param   string   $input      Input string to test.
	 * @param   string   $expected   Expected fetched string.
	 * @param   boolean  $exception  True if an InvalidArgumentException is expected based on invalid input.
	 *
	 * @return  void
	 *
	 * @covers        WebServiceErrors::checkSupressResponseCodes
	 * @dataProvider  seedCheckSupressResponseCodesData
	 * @since         1.0
	 */
	public function testCheckSupressResponseCodes($input,  $expected, $exception)
	{
		// Set the input values.
		$_GET['suppress_response_codes'] = $input;

		// If we are expecting an exception set it.
		if ($exception)
		{
			$this->setExpectedException('InvalidArgumentException');
		}

		// Execute the code to test.
		TestReflection::invoke($this->_instance, 'checkSupressResponseCodes');

		// Clean up after ourselves.
		$_GET['suppress_response_codes'] = null;

		// Verify the value.
		$this->assertEquals($expected, TestReflection::getValue($this->_instance, 'responseCode'));
	}

	/**
	 * Provides test data for request format detection.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedAddErrorData()
	{
		$errorsMap = json_decode("{
					\"301\":
						{
							\"code\": \"301\",
							\"message\": \"Message for code 301\",
							\"more_info\": \"More info fore code 301\",
							\"response_code\": \"401\"
						}
					}");

		$error['code'] = "foo";
		$error['message'] = 'This error is not known';
		$error['more_info'] = 'A link where to find more info about an unknown error';
		$error['response_code'] = '401';

		// Input, Expected, Exception
		return array(
				array("301", $errorsMap, get_object_vars($errorsMap->{"301"})),
				array("foo", $errorsMap, $error)
		);
	}

	/**
	 * Tests checkAddError()
	 *
	 * @param   string  $input     Input string to test.
	 * @param   array   $errorMap  The errors map
	 * @param   array   $expected  Expected fetched string.
	 *
	 * @return  void
	 *
	 * @covers        WebServiceErrors::addError
	 * @dataProvider  seedAddErrorData
	 * @since         1.0
	 */
	public function testAddError($input,  $errorMap, $expected)
	{
		// Execute the code to test.
		TestReflection::invoke($this->_instance, 'addError', $input);
		$actual = array_pop(TestReflection::getValue($this->_instance, 'errorsArray'));

		// Verify the value.
		$this->assertEquals($expected, $actual);
		$this->assertEquals(true, TestReflection::getValue($this->_instance, 'errors'));
		$this->assertEquals(401, TestReflection::getValue($this->_instance, 'responseCode'));
	}

	/**
	 * Tests unknownError()
	 *
	 * @return  void
	 *
	 * @covers        WebServiceErrors::unknownError
	 * @since         1.0
	 */
	public function testUnknownError()
	{
		$expected['code'] = "foo";
		$expected['message'] = 'This error is not known';
		$expected['more_info'] = 'A link where to find more info about an unknown error';
		$expected['response_code'] = '401';

		$actual = TestReflection::invoke($this->_instance, 'unknownError', $expected['code']);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Tests getErrors()
	 *
	 * @return  void
	 *
	 * @covers        WebServiceErrors::getErrors
	 * @since         1.0
	 */
	public function testGetErrors()
	{
		$expected = array('foo', 'bar');
		TestReflection::setValue($this->_instance, 'errorsArray', $expected);

		$actual = TestReflection::invoke($this->_instance, 'getErrors');

		$this->assertEquals($expected, $actual);
	}
}