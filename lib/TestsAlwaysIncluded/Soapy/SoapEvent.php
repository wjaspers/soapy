<?php

namespace TestsAlwaysIncluded\Soapy;

use Symfony\Component\EventDispatcher\Event;

class SoapEvent extends Event
{
	/**
	 * Holds the current SoapClient
	 * @var array
	 */
	protected $client;

	/**
	 * Holds the current SoapFault
	 * @var SoapFault
	 */
	protected $fault;

	/**
	 * Holds the current XML request
	 * @var string
	 */
	protected $request;

	/**
	 * Holds additional information about our request.
	 * @var RequestContext
	 */
	protected $requestContext;

	/**
	 * Holds the current XML response
	 * @var string
	 */
	protected $response;

	/**
	 * Sets the current SoapClient
	 * @param SoapClient $client
	 */
	public function setClient(SoapClient $client)
	{
		$this->client = $client;
	}

	/**
	 * Returns the current SoapClient
	 * @return SoapClient
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Sets the current SoapFault
	 * @param SoapFault $fault
	 */
	public function setFault($fault)
	{
		$this->fault = $fault;
	}

	/**
	 * Returns the current SoapFault
	 * @return SoapFault
	 */
	public function getFault()
	{
		return $this->fault;
	}

	/**
	 * Sets the current XML Request
	 * @param string $request
	 */
	public function setRequest($request)
	{
		$this->request = $request;
	}

	/**
	 * Returns the current XML request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Sets the RequestContext
	 * @param RequestContext $context
	 */
	public function setRequestContext(RequestContext $context)
	{
		$this->requestContext = $context;
	}

	/**
	 * Returns the RequestContext
	 * @return RequestContext
	 */
	public function getRequestContext()
	{
		return $this->requestContext;
	}

	/**
	 * Sets the current XML Response
	 * @param string $response
	 */
	public function setResponse($response)
	{
		$this->response = $response;
	}

	/**
	 * Returns the current XML Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Resets the SoapEvent's state.
	 */
	public function __clone()
	{
		$this->name = null;
		$this->propagationStopped = false;
	}
}
