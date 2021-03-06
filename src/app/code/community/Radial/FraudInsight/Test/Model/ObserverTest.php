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

class Radial_FraudInsight_Test_Model_ObserverTest
	extends EcomDev_PHPUnit_Test_Case
{

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::_handleProcessOrder()
	 * is invoked, it will instantiate the radial_fraudinsight/risk_fraud class passing to its constructor
	 * an array with key 'order' mapped to a sales/order object and another key 'helper' mapped
	 * to a radial_fraudinsight helper object. Then, it will call the method
	 * radial_fraudinsight/risk_fraud::process(). Finally, the method
	 * Radial_FraudInsight_Model_Observer::_handleProcessOrder() will return itself.
	 */
	public function testHandleProcessOrder()
	{
		/** @var Mage_Sales_Model_Order */
		$order = Mage::getModel('sales/order');
		/** @var Radial_FraudInsight_Helper_Data */
		$helper = Mage::helper('radial_fraudinsight');
		/** @var Mock_Radial_FraudInsight_Model_Risk_Fraud */
		$riskFraud = $this->getModelMock('radial_fraudinsight/risk_fraud', array('process'), false, array(array(
			// key 'order' is required.
			'order' => $order,
			// key 'helper' is optional.
			'helper' => $helper,
		)));
		$riskFraud->expects($this->once())
			->method('process')
			->will($this->returnSelf());
		$this->replaceByMock('model', 'radial_fraudinsight/risk_fraud', $riskFraud);

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = $this->getModelMock('radial_fraudinsight/observer', array(), false, array(array(
			// key 'helper' is optional.
			'helper' => $helper,
		)));

		$this->assertSame($observer, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$observer, '_handleProcessOrder', array($order)
		));
	}

	/**
	 * @return array
	 */
	public function providerIsValidOrder()
	{
		return array(
			array(Mage::getModel('sales/order'), true),
			array(null, true),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::_isValidOrder()
	 * will return true when the passed in parameter is a valid order object of type
	 * Mage_Sales_Model_Order. Otherwise it will return false if the passed in parameter
	 * is not a valid Mage_Sales_Model_Order order object.
	 *
	 * @param Mage_Sales_Model_Order | null
	 * @param bool
	 * @dataProvider providerIsValidOrder
	 */
	public function testIsValidOrder($order, $isValid)
	{
		/** @var Mage_Sales_Model_Order */
		$order = Mage::getModel('sales/order');

		/** @var Radial_FraudInsight_Model_Observer */
		$observer = Mage::getModel('radial_fraudinsight/observer');

		$this->assertSame($isValid, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$observer, '_isValidOrder', array($order)
		));
	}

	/**
	 * @return array
	 */
	public function providerHandleSalesModelServiceQuoteSubmitAfter()
	{
		$observerA = new Varien_Event_Observer(array('event' => new Varien_Event(array(
			'order' => Mage::getModel('sales/order'),
		))));
		$observerB = new Varien_Event_Observer(array('event' => new Varien_Event(array(
			'order' => null,
		))));
		return array(
			array($observerA),
			array($observerB),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::handleSalesModelServiceQuoteSubmitAfter()
	 * will be passed in a Varien_Event_Observer object as its parameter. It will then, invoked
	 * Radial_FraudInsight_Model_Observer::_isValidOrder() method passing in the object parameter
	 * if the object parameter is a valid sales/order object it will return true otherwise it will return false.
	 * If it returns true then the method Radial_FraudInsight_Model_Observer::_handleProcessOrder()
	 * will be invoked. Otherwise if it returns false, then the method Radial_FraudInsight_Model_Observer::_logWarning()
	 * will be called. Finally, the method Radial_FraudInsight_Model_Observer::handleSalesModelServiceQuoteSubmitAfter() will return itself.
	 *
	 * @param Varien_Event_Observer
	 * @dataProvider providerHandleSalesModelServiceQuoteSubmitAfter
	 */
	public function testHandleSalesModelServiceQuoteSubmitAfter(Varien_Event_Observer $event)
	{
		/** @var Mage_Sales_Model_Order | null */
		$order = $event->getEvent()->getOrder();

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = $this->getModelMock('radial_fraudinsight/observer', array('_isValidOrder', '_handleProcessOrder', '_logWarning'));
		$observer->expects($this->once())
			->method('_isValidOrder')
			->will($this->returnCallback(function($object) {return !is_null($object);}));
		$observer->expects($order? $this->once() : $this->never())
			->method('_handleProcessOrder')
			->with($this->identicalTo($order))
			->will($this->returnSelf());
		$observer->expects($order? $this->never() : $this->once())
			->method('_logWarning')
			->with($this->isType('string'))
			->will($this->returnSelf());
		$this->assertSame($observer, $observer->handleSalesModelServiceQuoteSubmitAfter($event));
	}

	/**
	 * @return array
	 */
	public function providerHandleProcessMultipleOrders()
	{
		return array(
			array(array(Mage::getModel('sales/order'), null)),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::_handleProcessMultipleOrders()
	 * will be passed in an array of objects as parameter it will then loop through each object.
	 * For each object it will then, invoked Radial_FraudInsight_Model_Observer::_isValidOrder() method
	 * passing in the object parameter if the object parameter is a valid sales/order object it will return
	 * true otherwise it will return false. If it return true then the method Radial_FraudInsight_Model_Observer::_handleProcessOrder()
	 * will be invoked. Otherwise if it returns false, then the method Radial_FraudInsight_Model_Observer::_logWarning()
	 * will be called. Finally, the method Radial_FraudInsight_Model_Observer::_handleProcessMultipleOrders() will return itself.
	 *
	 * @param array
	 * @dataProvider providerHandleProcessMultipleOrders
	 */
	public function testHandleProcessMultipleOrders(array $orders)
	{
		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = $this->getModelMock('radial_fraudinsight/observer', array('_isValidOrder', '_handleProcessOrder', '_logWarning'));
		$observer->expects($this->exactly(2))
			->method('_isValidOrder')
			->will($this->returnValueMap(array(
				array($orders[0], true),
				array($orders[1], false),
			)));
		$observer->expects($this->once())
			->method('_handleProcessOrder')
			->with($this->identicalTo($orders[0]))
			->will($this->returnSelf());
		$observer->expects($this->once())
			->method('_logWarning')
			->with($this->isType('string'))
			->will($this->returnSelf());
		$this->assertSame($observer, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$observer, '_handleProcessMultipleOrders', array($orders)
		));
	}

	/**
	 * @return array
	 */
	public function providerHandleCheckoutSubmitAllAfter()
	{
		$observerA = new Varien_Event_Observer(array('event' => new Varien_Event(array(
			'orders' => array(Mage::getModel('sales/order'), null),
		))));
		$observerB = new Varien_Event_Observer(array('event' => new Varien_Event(array(
			'orders' => array(),
		))));
		return array(
			array($observerA),
			array($observerB),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::handleCheckoutSubmitAllAfter()
	 * will be passed in a Varien_Event_Observer object as its parameter. It will it get the array of orders
	 * from the passing Varien_Event_Observer object. If the array is not empty then the method
	 * Radial_FraudInsight_Model_Observer::_handleProcessMultipleOrders() will be invoked.
	 * Otherwise the method Radial_FraudInsight_Model_Observer::_logWarning() will be called.
	 * Finally, the method Radial_FraudInsight_Model_Observer::handleCheckoutSubmitAllAfter() will return itself.
	 *
	 * @param Varien_Event_Observer
	 * @dataProvider providerHandleCheckoutSubmitAllAfter
	 */
	public function testHandleCheckoutSubmitAllAfter(Varien_Event_Observer $event)
	{
		/** @var Mage_Sales_Model_Order | null */
		$orders = $event->getEvent()->getOrders();

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = $this->getModelMock('radial_fraudinsight/observer', array('_handleProcessMultipleOrders', '_logWarning'));
		$observer->expects(empty($orders) ? $this->never() : $this->once())
			->method('_handleProcessMultipleOrders')
			->with($this->identicalTo($orders))
			->will($this->returnSelf());
		$observer->expects(empty($orders) ? $this->once() : $this->never())
			->method('_logWarning')
			->with($this->isType('string'))
			->will($this->returnSelf());
		$this->assertSame($observer, $observer->handleCheckoutSubmitAllAfter($event));
	}

	/**
	 * @return array
	 */
	public function providerDetectFraudulentOrders()
	{
		return array(
			array(true),
			array(false),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::detectFraudulentOrders()
	 * it will invoke the method Radial_FraudInsight_Helper_Config::isEnabled() if this method
	 * return true will continue to instantiate a radial_fraudinsight/risk_order object and passed
	 * an array with key 'helper' mapped to an instance of Radial_FraudInsight_Helper_Data. Then,
	 * it will invoke the method radial_fraudinsight/risk_order::process(). Finally, the method
	 * Radial_FraudInsight_Model_Observer::detectFraudulentOrders() will then return itself.
	 * However, if the method Radial_FraudInsight_Helper_Config::isEnabled() returns false,
	 * it will simply return itself.
	 *
	 * @param bool
	 * @dataProvider providerDetectFraudulentOrders
	 */
	public function testDetectFraudulentOrders($isEnabled)
	{
		/** @var Radial_FraudInsight_Helper_Data */
		$helper = Mage::helper('radial_fraudinsight');

		/** @var Mock_Radial_FraudInsight_Model_Risk_Order */
		$riskOrder = $this->getModelMock('radial_fraudinsight/risk_order', array('process'), false, array(array(
			'helper' => $helper,
		)));
		// If the parameter $isEnabled is true this method will called once
		// otherwise it will never be called.
		$riskOrder->expects($isEnabled ? $this->once() : $this->never())
			->method('process')
			->will($this->returnSelf());
		$this->replaceByMock('model', 'radial_fraudinsight/risk_order', $riskOrder);

		/** @var Mock_Radial_FraudInsight_Helper_Config */
		$config = $this->getHelperMock('radial_fraudinsight/config', array('isEnabled'));
		$config->expects($this->once())
			->method('isEnabled')
			->will($this->returnValue($isEnabled));

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = Mage::getModel('radial_fraudinsight/observer', array(
			'config' => $config,
			'helper' => $helper,
		));

		$this->assertSame($observer, $observer->detectFraudulentOrders());
	}

	/**
	 * @return array
	 */
	public function providerHandleSalesOrderSaveAfter()
	{
		$observerA = new Varien_Event_Observer(array('event' => new Varien_Event(array(
			'order' => Mage::getModel('sales/order'),
		))));
		$observerB = new Varien_Event_Observer(array('event' => new Varien_Event(array(
			'order' => null,
		))));
		return array(
			array($observerA),
			array($observerB),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::handleSalesOrderSaveAfter()
	 * will be passed in a Varien_Event_Observer object as its parameter. It will then, invoked
	 * Radial_FraudInsight_Model_Observer::_isValidOrder() method passing in the object parameter
	 * if the object parameter is a valid sales/order object it will return true otherwise it will return false.
	 * If it returns true then the method Radial_FraudInsight_Model_Observer::_handleOrderFeedback()
	 * will be invoked. Otherwise if it returns false, then the method Radial_FraudInsight_Model_Observer::_logWarning()
	 * will be called. Finally, the method Radial_FraudInsight_Model_Observer::handleSalesOrderSaveAfter() will return itself.
	 *
	 * @param Varien_Event_Observer
	 * @dataProvider providerHandleSalesOrderSaveAfter
	 */
	public function testhandleSalesOrderSaveAfter(Varien_Event_Observer $event)
	{
		/** @var Mage_Sales_Model_Order | null */
		$order = $event->getEvent()->getOrder();

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = $this->getModelMock('radial_fraudinsight/observer', array('_isValidOrder', '_handleOrderFeedback', '_logWarning'));
		$observer->expects($this->once())
			->method('_isValidOrder')
			->will($this->returnCallback(function($object) {return !is_null($object);}));
		$observer->expects($order ? $this->once() : $this->never())
			->method('_handleOrderFeedback')
			->with($this->identicalTo($order))
			->will($this->returnSelf());
		$observer->expects($order ? $this->never() : $this->once())
			->method('_logWarning')
			->with($this->isType('string'))
			->will($this->returnSelf());
		$this->assertSame($observer, $observer->handleSalesOrderSaveAfter($event));
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::_handleOrderFeedback()
	 * is invoked and will be passed in a sales/order object. Then, it will call the method
	 * Radial_FraudInsight_Helper_Data::getRiskInsight() passing in as parameter a the
	 * sales/order object, it will then return a radial_fraudinsight/risk_insight object.
	 * Then, it will call the method Radial_FraudInsight_Helper_Data::canHandleFeedback()
	 * passing in the sales/order object as first parameter and the
	 * radial_fraudinsight/risk_insight object as second parameter. If the method
	 * Radial_FraudInsight_Helper_Data::canHandleFeedback() return true, then it will proceed
	 * invoked the method Radial_FraudInsight_Model_Observer::_processOrderFeedback() passing
	 * in as first parameter the sales/order object and as second parameter the
	 * radial_fraudinsight/risk_insight object. Finally, the method
	 * Radial_FraudInsight_Model_Observer::_handleOrderFeedback() will return itself.
	 * Otherwise, if the method Radial_FraudInsight_Helper_Data::canHandleFeedback() returns false,
	 * it will skip everything and simply return itself.
	 */
	public function testHandleOrderFeedback()
	{
		/** @var bool */
		$canHandleFeedback = true;
		/** @var Mage_Sales_Model_Order */
		$order = Mage::getModel('sales/order');
		/** @var Radial_FraudInsight_Model_Risk_Insight */
		$riskInsight = Mage::getModel('radial_fraudinsight/risk_insight');
		/** @var Radial_FraudInsight_Helper_Data */
		$helper = $this->getHelperMock('radial_fraudinsight', array('getRiskInsight', 'canHandleFeedback'));
		$helper->expects($this->once())
			->method('getRiskInsight')
			->with($this->identicalTo($order))
			->will($this->returnValue($riskInsight));
		$helper->expects($this->once())
			->method('canHandleFeedback')
			->with($this->identicalTo($order), $this->identicalTo($riskInsight))
			->will($this->returnValue($canHandleFeedback));

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = $this->getModelMock('radial_fraudinsight/observer', array('_processOrderFeedback'), false, array(array(
			// key 'helper' is optional.
			'helper' => $helper,
		)));
		$observer->expects($this->once())
			->method('_processOrderFeedback')
			->with($this->identicalTo($order), $this->identicalTo($riskInsight))
			->will($this->returnSelf());

		$this->assertSame($observer, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$observer, '_handleOrderFeedback', array($order)
		));
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::_processOrderFeedback()
	 * is invoked, it will instantiate the radial_fraudinsight/send_feedback class passing to its constructor
	 * an array with key 'order' mapped to a sales/order object and another key 'insight' mapped
	 * to a radial_fraudinsight/risk_insight object. Then, it will call the method
	 * radial_fraudinsight/send_feedback::send(). Finally, the method
	 * Radial_FraudInsight_Model_Observer::_processOrderFeedback() will return itself.
	 */
	public function testProcessOrderFeedback()
	{
		/** @var Mage_Sales_Model_Order */
		$order = Mage::getModel('sales/order');
		/** @var Radial_FraudInsight_Model_Risk_Insight */
		$riskInsight = Mage::getModel('radial_fraudinsight/risk_insight');
		/** @var Mock_Radial_FraudInsight_Model_Send_Feedback */
		$sendFeedback = $this->getModelMock('radial_fraudinsight/send_feedback', array('send'), false, array(array(
			// key 'order' is required.
			'order' => $order,
			// key 'insight' is required.
			'insight' => $riskInsight,
		)));
		$sendFeedback->expects($this->once())
			->method('send')
			->will($this->returnSelf());
		$this->replaceByMock('model', 'radial_fraudinsight/send_feedback', $sendFeedback);

		/** @var Radial_FraudInsight_Model_Observer */
		$observer = Mage::getModel('radial_fraudinsight/observer');

		$this->assertSame($observer, EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$observer, '_processOrderFeedback', array($order, $riskInsight)
		));
	}

	/**
	 * @return array
	 */
	public function providerResendFeedbacks()
	{
		return array(
			array(true),
			array(false),
		);
	}

	/**
	 * Test that the method Radial_FraudInsight_Model_Observer::resendFeedbacks()
	 * it will invoke the method Radial_FraudInsight_Helper_Config::isEnabled() if this method
	 * return true will continue to instantiate a radial_fraudinsight/cron_feedback object and passed
	 * an array with key 'helper' mapped to an instance of Radial_FraudInsight_Helper_Data. Then,
	 * it will invoke the method radial_fraudinsight/cron_feedback::process(). Finally, the method
	 * Radial_FraudInsight_Model_Observer::resendFeedbacks() will then return itself.
	 * However, if the method Radial_FraudInsight_Helper_Config::isEnabled() returns false,
	 * it will simply return itself.
	 *
	 * @param bool
	 * @dataProvider providerResendFeedbacks
	 */
	public function testResendFeedbacks($isEnabled)
	{
		/** @var Radial_FraudInsight_Helper_Data */
		$helper = Mage::helper('radial_fraudinsight');

		/** @var Mock_Radial_FraudInsight_Model_Cron_Feedback */
		$cronFeedback = $this->getModelMock('radial_fraudinsight/cron_feedback', array('process'), false, array(array(
			'helper' => $helper,
		)));
		// If the parameter $isEnabled is true this method will called once
		// otherwise it will never be called.
		$cronFeedback->expects($isEnabled ? $this->once() : $this->never())
			->method('process')
			->will($this->returnSelf());
		$this->replaceByMock('model', 'radial_fraudinsight/cron_feedback', $cronFeedback);

		/** @var Mock_Radial_FraudInsight_Helper_Config */
		$config = $this->getHelperMock('radial_fraudinsight/config', array('isEnabled'));
		$config->expects($this->once())
			->method('isEnabled')
			->will($this->returnValue($isEnabled));

		/** @var Mock_Radial_FraudInsight_Model_Observer */
		$observer = Mage::getModel('radial_fraudinsight/observer', array(
			'config' => $config,
			'helper' => $helper,
		));

		$this->assertSame($observer, $observer->resendFeedbacks());
	}
}
