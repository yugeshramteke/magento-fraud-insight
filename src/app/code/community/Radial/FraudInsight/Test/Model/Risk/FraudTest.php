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

class Radial_FraudInsight_Test_Model_Risk_FraudTest
	extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * Test that the proper data is save to the risk insight
	 * database table given a valid sales/order object.
	 */
	public function testProcess()
	{
		$incrementId = '10009990211';
		$host = 'example.com';
		$accept = 'text/javascript, text/html, application/xml, text/xml, */*';
		$userAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:36.0) Gecko/20100101 Firefox/36.0';
		$headers = json_encode(array(
			'Accept' => $accept,
			'Host' => $host,
			'User-Agent' => $userAgent,
		));

		$order = Mage::getModel('sales/order', array('increment_id' => $incrementId));

		$_SERVER['HTTP_ACCEPT'] = $accept;
		$_SERVER['HTTP_HOST'] = $host;
		$_SERVER['HTTP_USER_AGENT'] = $userAgent;

		$insight = $this->getModelMock('radial_fraudinsight/risk_insight', array('save', 'addData'));
		$insight->expects($this->once())
			->method('save')
			->will($this->returnSelf());
		$insight->expects($this->once())
			->method('addData')
			->with($this->identicalTo(array(
				'order_increment_id' => $incrementId,
				'http_headers' => $headers,
				'is_request_sent' => 0,
			)))
			->will($this->returnSelf());
		$this->replaceByMock('model', 'radial_fraudinsight/risk_insight', $insight);

		$fraud = Mage::getModel('radial_fraudinsight/risk_fraud', array('order' => $order));
		$this->assertSame($fraud, $fraud->process());
	}

	/**
	 * Test that the no data is save to the risk insight table
	 * when invalid order object is passed in the constructor.
	 */
	public function testProcessInvalidOrder()
	{
		$incrementId = null;
		$order = Mage::getModel('sales/order', array('increment_id' => $incrementId));

		$insight = $this->getModelMock('radial_fraudinsight/risk_insight', array('save'));
		$insight->expects($this->never())
			->method('save');
		$this->replaceByMock('model', 'radial_fraudinsight/risk_insight', $insight);

		$fraud = Mage::getModel('radial_fraudinsight/risk_fraud', array('order' => $order));
		$this->assertSame($fraud, $fraud->process());
	}
}
