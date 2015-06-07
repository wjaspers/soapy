<?php

namespace TestsAlwaysIncluded\Soapy\EventSubscribers;

use Psr\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TestsAlwaysIncluded\Soapy\SoapEvent;
use TestsAlwaysIncluded\Soapy\SoapEvents;

/**
 * Logs SOAP requests and responses.
 * Inspired by the PHPTools LoggingSoapClient created by Chris McMacken & Team
 * @link https://github.com/chrismcmacken/phptools/tree/master/loggingsoapclient
 */
class LogSubscriber implements EventSubscriberInterface
{
	/**
	 * Holds the logger.
	 * @var string|false
	 */
	protected $logger;

	/**
	 * @param Logger $logger 
	 */
	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @inheritdoc
	 * @return  array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			SoapEvents::REQUEST_BEFORE => array('onBeforeRequest', 0),
			SoapEvents::RESPONSE => array('onResponse', 0),
			SoapEvents::FAULT => array('onSoapFault', 0),
		);
	}

	/**
	 * Sets the logger.
	 * @param Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * Logs the request.
	 * @param SoapEvent $event
	 */
	public function onBeforeRequest(SoapEvent $event)
	{
		$message = '[SOAP] Request:%s';
		$client = $event->getClient();
		$context = $client->getRequestContext();
		$action = $context->action ?: $client->__getWsdl();
		$request = $event->getRequest();
		$parameters = $context->all();
		$this->logger->info(sprintf($message, $action), compact('request', 'parameters'));
	}

	/**
	 * Logs the response.
	 * @param SoapEvent $event
	 */
	public function onResponse(SoapEvent $event)
	{
		$message = '[SOAP] Response:%s';
		$client = $event->getClient();
		$context = $client->getRequestContext();
		$action = $context->action ?: $client->__getWsdl();
		$response = $event->getResponse();
		$parameters = $context->all();
		$this->logger->info(sprintf($message, $action), compact('response', 'parameters'));
	}

	/**
	 * Logs the SoapFault
	 * @param SoapEvent $event
	 */
	public function onSoapFault(SoapEvent $event)
	{
		$message = '[SOAP] Fault:%s';
		$client = $event->getClient();
		$context = $client->getRequestContext();
		$action = $context->action ?: $client->__getWsdl();
		$fault = $event->getFault();
		$this->logger->info(sprintf($message, $action), compact('fault'));
	}
}
