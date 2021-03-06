<?php
/**
 * Copyright (c) 2013-2016 Radial Commerce Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright   Copyright (c) 2013-2016 Radial Commerce Inc. (http://www.radial.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Radial_FraudInsight_Test_Lib_Sdk_RequestTest
	extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * Create a new payload and set any data passed in the properties parameter.
	 * Each key in array should be a setter method to call and will be given
	 * the value at that key.
	 *
	 * @param  array
	 * @return Radial_FraudInsight_Sdk_IPayload
	 */
	protected function _buildPayload(array $properties=array())
	{
		$payload = $this->_createNewPayload();
		foreach ($properties as $setterMethod => $value) {
			$payload->$setterMethod($value);
		}
		return $payload;
	}

	/**
	 * Create a new order Request payload.
	 *
	 * @return Radial_FraudInsight_Sdk_IPayload
	 */
	protected function _createNewPayload()
	{
		return new Radial_FraudInsight_Sdk_Request();
	}

	/**
	 * Return a C14N, whitespace removed, XML string.
	 *
	 * @param  string
	 * @return string
	 */
	protected function _loadXmlTestString($fixtureFile)
	{
		$dom = new DOMDocument();
		$dom->load($fixtureFile);
		$dom->encoding = 'utf-8';
		$dom->formatOutput = false;
		$dom->preserveWhiteSpace = false;
		$dom->normalizeDocument();
		return $dom->saveXML();
	}

	/**
	 * Provide paths to fixture files containing valid serializations of
	 * order Request payloads.
	 *
	 * @return array
	 */
	public function provideRequestSerializedDataFile()
	{
		return array(
			array(__DIR__ . '/RequestTest/fixtures/RiskInsightRequest.xml'),
			array(__DIR__ . '/RequestTest/fixtures/RiskInsightRequestMinimalData.xml'),
		);
	}

	/**
	 * Test deserializing data into a payload and then deserializing back
	 * to match the original data.
	 *
	 * @param string $serializedDataFile - path to fixture file
	 * @dataProvider provideRequestSerializedDataFile
	 */
	public function testRequestDeserializeSerialize($serializedDataFile)
	{
		$payload = $this->_buildPayload();
		$serializedData = $this->_loadXmlTestString($serializedDataFile);
		$payload->deserialize($serializedData);
		$this->assertXmlStringEqualsXmlString($serializedData, $payload->serialize());
	}

	/**
	 * @return array
	 */
	public function providerRequestInvlidPayload()
	{
		return array(
			array(__DIR__ . '/RequestTest/fixtures/InvalidPayload.xml'),
		);
	}

	/**
	 * @param string $serializedDataFile - path to fixture file
	 * @expectedException Radial_FraudInsight_Sdk_Exception_Invalid_Payload_Exception
	 * @dataProvider providerRequestInvlidPayload
	 */
	public function testRequestInvlidPayload($serializedDataFile)
	{
		$payload = $this->_buildPayload();
		$serializedData = $this->_loadXmlTestString($serializedDataFile);
		$payload->deserialize($serializedData);
	}
}
