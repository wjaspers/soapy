<?php

namespace TestsAlwaysIncluded\Services;

use SoapClient as BaseSoapClient;
use SoapFault;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SoapClient extends BaseSoapClient
{
	/** @var EventDispatcher */
	protected $dispatcher;

	/** @var array */
	protected $options;

	/** @var string */
	protected $wsdl;

	/**
	 * Creates a SoapClient
	 * @param string $wsdl
	 * @param array $options
	 * @param EventDispatcher $dispatcher
	 */
	public function __construct($wsdl, $options = array(), EventDispatcher $dispatcher = null)
	{
		$this->wsdl = $wsdl;
		$this->options = $options;
		$this->dispatcher = $dispatcher ?: new EventDispatcher();
		// We'll wait to call the parent's __construct
		// because wsdl mode attempts to open a stream
		// context immediately, which is painful at
		// best to work with. We can then also tune
		// options before the request starts.
	}

	/**
	 * Attaches the EventSubscribers callbacks to the dispatcher.
	 * @param EventSubscriberInterface $subscriber
	 */
	public function __addSubscriber(EventSubscriberInterface $subscriber) {
		$this->dispatcher->addSubscriber($subscriber);
	}

	/**
	 * Removes the EventSubscribers callbacks from the dispatcher.
	 * @param EventSubscriberInterface $subscriber
	 */
	public function __removeSubscriber(EventSubscriberInterface $subscriber)
	{
		$this->dispatcher->removeSubscriber($subscriber);
	}


	/**
	 * Sets the event dispatcher
	 * @param EventDispatcher $dispatcher
	 */
	public function __setEventDispatcher(EventDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Returns the event dispatcher
	 * @return EventDispatcher
	 */
	public function __getEventDispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * @param array $options
	 */
	public function __setOptions($options = array())
	{
		$this->options = $options;
	}

	/**
	 * @return array
	 */
	public function __getOptions()
	{
		return $this->options;
	}

	/**
	 * Sets the WSDL document URI to use.
	 * @param string $wsdl
	 */
	public function __setWsdl($wsdl)
	{
		$this->wsdl = $wsdl;
	}

	/**
	 * Returns the URI to the WSDL.
	 * @return string
	 */
	public function __getWsdl()
	{
		return $this->wsdl;
	}

	/**
	 * Helper function to initialize the client
	 * before a request.
	 */
	protected function __constructDelayed()
	{
		// Let callers modify our wsdl and options before we execute an action.
		$this->__fireEvent(SoapEvents::CONSTRUCT, new SoapEvent());
		parent::__construct($this->wsdl, $this->options);
	}

	/**
	 * Overrides the PHP provided method to execute the request.
	 *
	 * @param string $request	XML Document
	 * @param string $location	URI to make the HTTP request against
	 * @param string $action	WSDL Action to execute (if any)
	 * @param string $version	SOAP Version to use
	 * @param int $one_way		Whether or not we expect a response.
	 * @return string		XML Document
	 * @throws SoapFault		Any Fault triggered by the action.
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0)
	{
		// Retreieves all inputs.
		$requestContext = new RequestContext(get_defined_vars());
		$event = new SoapEvent();
		$event->setRequestContext($requestContext);
		$this->__fireEvent(SoapEvents::REQUEST_BEFORE, $event);

		// Prevent the context from alteration.
		$requestContext->lock();

		// Detect if the user modified anything about the request.
		// Do not allow unexpected data into the current scope.
		extract($requestContext->all(), EXTR_IF_EXISTS);

		// Bail out if the request was stopped.
		if ($event->isPropagationStopped()) {
			if ($one_way) {
				// TODO I dont know what the normal behavior is if a fault occurs on a one way request
				return '';
			}
			
			$this->__fireEvent(SoapEvents::RESPONSE, $event = clone $event);

			return $event->getResponse();
		}

		$response = parent::__doRequest($request, $location, $action, $version, $one_way);

		if (empty($one_way)) {
			$event->setResponse($response);
			$this->__fireEvent(SoapEvents::RESPONSE_RECEIVED, $event = clone $event);
		}

		$this->__fireEvent(SoapEvents::RESPONSE, $event = clone $event);

		return $event->getResponse();
	}

	/**
	 * Wraps the existing __call method.
	 *
	 * @param string $method	A service method to execute.
	 * @param array $arguments	SoapVars to send.
	 * @return mixed		
	 * @throws mixed		Exception or SoapFault
	 */
	public function __call($method, $arguments)
	{
		$this->__constructDelayed();

		try {
			return parent::__call($method, $arguments);
		} catch (SoapFault $fault) {
			$event = new SoapEvent();
			$event->setFault($fault);
			$this->__fireEvent(SoapEvents::FAULT, $event);
			// Any positive, non-zero value is truthy.
			if (! empty($this->options['exceptions'])) {
				throw $fault;
			}

			return $event->getResponse();
		}
	}

	/**
	 * Dispatches a SoapEvent.
	 * This is useful for making modifications to the request, response,
	 * swallowing faults, or changing options.
	 *
	 * @param string $name		The event to dispatch.
	 * @param SoapEvent $event	The SoapEvent container.
	 * @return SoapEvent
	 */
	protected function __fireEvent($name, SoapEvent $event)
	{
		$event->setClient($this);
		return $this->dispatcher->dispatch($name, $event);
	}
}
