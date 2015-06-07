<?php

namespace TestsAlwaysIncluded\Soapy\EventSubscribers;

use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TestsAlwaysIncluded\Soapy\SoapEvent;
use TestsAlwaysIncluded\Soapy\SoapEvents;

class MockSubscriber implements EventSubscriberInterface
{
	/**
	 * Holds the next response to provide.
	 * @var string|false
	 */
	protected $nextResponse = false;

	/** 
	 * Holds a list of responses to use.
	 * @var array
	 */
	protected $responses = array();

	/**
	 * Determines if we should restart serving responses
	 * after we've reached the end of the list.
	 * @var boolean
	 */
	protected $wrap = false;

	/**
	 * Allows the user to override the wsdl.
	 * This is useful for utilizing a local file for unit tests.
	 * @var string
	 */
	protected $wsdl;

	/**
	 * @inheritdoc
	 * @return  array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			SoapEvents::CONSTRUCT => array('onConstruct', 1000),
			SoapEvents::REQUEST_BEFORE => array('onBeforeRequest', 1000),
		);
	}

	/**
	 * Overrides the next response.
	 * Set to false to stop serving responses.
	 * @param string|false $nextResponse
	 */
	public function setNextResponse($nextResponse)
	{
		$this->nextResponse = $nextResponse
	}

	/**
	 * Returns the next response
	 * @return string|false
	 */
	public function getNextResponse()
	{
		return $this->nextResponse;
	}

	/**
	 * Sets the list of responses.
	 * @param array $responses
	 */
	public function setResponses($responses)
	{
		$this->responses = $responses;
	}

	/**
	 * Returns the list of responses.
	 * @return array
	 */
	public function getResponses()
	{
		return $this->responses;
	}

	/**
	 * Determines if the subscriber should start over
	 * after the last response is served.
	 * @param boolean $wrap
	 */
	public function setWrap($wrap)
	{
		$this->wrap = $wrap;
	}

	/**
	 * @return boolean
	 */
	public function getWrap()
	{
		return $this->wrap;
	}

	/**
	 * Overrides the wsdl.
	 * @param string $wsdl
	 */
	public function setWsdl($wsdl)
	{
		$this->wsdl = $wsdl;
	}

	/**
	 * @return string
	 */
	public function getWsdl()
	{
		return $this->wsdl;
	}

	/**
	 * Adds a response to the queue.
	 * @param string $response
	 */
	public function addResponse($response)
	{
		$this->responses[] = $response;
		$this->nextResponse = current($this->responses);
	}

	/**
	 * Allows the construction phase to be modified.
	 * @param SoapEvent $event
	 */
	public function onConstruct(SoapEvent $event)
	{
		if ($this->wsdl) {
			$event->getClient()->__setWsdl($this->wsdl);
		}
	}

	/**
	 * Interrupts the current request and substitutes a response.
	 * @param SoapEvent $event
	 * @throws RuntimeException
	 */
	public function onBeforeRequest(SoapEvent $event)
	{
		if (false === $this->nextResponse) {
			throw new RuntimeException('MockSubscriber did not provide a response.');
		}

		if (is_readable($this->nextResponse)) {
			$event->setResponse(file_get_contents($this->nextResponse));
		} else {
			throw new RuntimeException('Response provided to the MockSubscriber is not readable!');
		}

		$this->nextResponse = next($this->responses);
		if (false === $this->nextResponse && true === $this->wrap) {
			$this->nextResponse = reset($this->responses);
		}

		$event->stopPropagation();
	}
}
