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

class Radial_FraudInsight_Sdk_Payment_Card
	extends Radial_FraudInsight_Sdk_Payload
	implements Radial_FraudInsight_Sdk_Payment_ICard
{
	/** @var string */
	protected $_cardHolderName;
	/** @var string */
	protected $_paymentAccountUniqueId;
	/** @var bool */
	protected $_isToken;
	/** @var string */
	protected $_paymentAccountBin;
	/** @var string */
	protected $_expireDate;
	/** @var string */
	protected $_cardType;

	public function __construct(array $initParams=array())
	{
		parent::__construct($initParams);
		$this->_optionalExtractionPaths = array(
			'setCardHolderName' => 'x:CardHolderName',
			'setPaymentAccountUniqueId' => 'x:PaymentAccountUniqueId',
			'setPaymentAccountBin' => 'x:PaymentAccountBin',
			'setExpireDate' => 'x:ExpireDate',
			'setCardType' => 'x:CardType',
		);
		$this->_booleanExtractionPaths = array(
			'setIsToken' => 'string(x:PaymentAccountUniqueId/@isToken)',
		);
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::getCardHolderName()
	 */
	public function getCardHolderName()
	{
		return $this->_cardHolderName;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::setCardHolderName()
	 */
	public function setCardHolderName($cardHolderName)
	{
		$this->_cardHolderName = $cardHolderName;
		return $this;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::getPaymentAccountUniqueId()
	 */
	public function getPaymentAccountUniqueId()
	{
		return $this->_paymentAccountUniqueId;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::setPaymentAccountUniqueId()
	 */
	public function setPaymentAccountUniqueId($paymentAccountUniqueId)
	{
		$this->_paymentAccountUniqueId = $paymentAccountUniqueId;
		return $this;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::getIsToken()
	 */
	public function getIsToken()
	{
		return $this->_isToken;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::setIsToken()
	 */
	public function setIsToken($isToken)
	{
		$this->_isToken = $isToken;
		return $this;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::getPaymentAccountBin()
	 */
	public function getPaymentAccountBin()
	{
		return $this->_paymentAccountBin;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::setPaymentAccountBin()
	 */
	public function setPaymentAccountBin($paymentAccountBin)
	{
		$this->_paymentAccountBin = $paymentAccountBin;
		return $this;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::getExpireDate()
	 */
	public function getExpireDate()
	{
		return $this->_expireDate;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::setExpireDate()
	 */
	public function setExpireDate($expireDate)
	{
		$this->_expireDate = $expireDate;
		return $this;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::getCardType()
	 */
	public function getCardType()
	{
		return $this->_cardType;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payment_ICard::setCardType()
	 */
	public function setCardType($cardType)
	{
		$this->_cardType = $cardType;
		return $this;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payload::_canSerialize()
	 */
	protected function _canSerialize()
	{
		return (trim($this->_serializeContents()) !== '');
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payload::_getRootNodeName()
	 */
	protected function _getRootNodeName()
	{
		return static::ROOT_NODE;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payload::_getXmlNamespace()
	 */
	protected function _getXmlNamespace()
	{
		return self::XML_NS;
	}

	/**
	 * @see Radial_FraudInsight_Sdk_Payload::_serializeContents()
	 */
	protected function _serializeContents()
	{
		return $this->_serializeOptionalValue('CardHolderName', $this->getCardHolderName())
			. $this->_serializePaymentAccountUniqueId()
			. $this->_serializeOptionalValue('PaymentAccountBin', $this->getPaymentAccountBin())
			. $this->_serializeOptionalValue('ExpireDate', $this->getExpireDate())
			. $this->_serializeOptionalValue('CardType', $this->getCardType());
	}

	/**
	 * Serialize the payment account unique id node if there's a valid value.
	 *
	 * @return string
	 */
	protected function _serializePaymentAccountUniqueId()
	{
		$isToken = $this->getIsToken();
		$isToken = !is_null($isToken) ? $isToken : false;
		$paymentAccountUniqueId = $this->getPaymentAccountUniqueId();
		return $paymentAccountUniqueId ? "<PaymentAccountUniqueId isToken=\"{$isToken}\">{$paymentAccountUniqueId}</PaymentAccountUniqueId>" : '';
	}
}
