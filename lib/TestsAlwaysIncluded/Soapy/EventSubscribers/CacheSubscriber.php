<?php

namespace TestsAlwaysIncluded\Soapy\EventSubscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TestsAlwaysIncluded\Soapy\SoapEvent;
use TestsAlwaysIncluded\Soapy\SoapEvents;

/**
 * Caches responses.
 */
class CacheSubscriber implements EventSubscriberInterface
{
	/**
	 * Holds the Cache plugin.
	 * @var mixed
	 */
	protected $cache;

	/**
	 * Holds the current cache key.
	 * @var string
	 */
	protected $key;

	/**
	 * Holds the lifetime of the cache.
	 * Defaults to 1 because 0 sometimes means forever.
	 * @var int
	 */
	protected $lifetime = 1; 

	/**
	 * @param cache $cache 
	 */
	public function __construct($cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @inheritdoc
	 * @return  array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			SoapEvents::REQUEST_BEFORE => array('onBeforeRequest', 0),
			SoapEvents::RESPONSE_RECEIVED => array('onResponseRecieved', 0),
			SoapEvents::FAULT => array('onSoapFault', 0),
		);
	}

	/**
	 * Sets the Cache client.
	 * @param mixed $cache
	 */
	public function setCache($cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @return mixed
	 */
	public function getCache()
	{
		return $this->cache;
	}

	/**
	 * Sets the current cache key
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * Returns the current cache key
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Sets the cache lifetime
	 * @param int $lifetime
	 */
	public function setLifetime($lifetime)
	{
		$this->lifetime = $lifetime;
	}

	/**
	 * Returns the cache lifetime.
	 * @return int
	 */
	public function getLifetime()
	{
		return $this->lifetime;
	}

	/**
	 * Inspects the request and attempts to substitute a response, if available.
	 * @param SoapEvent $event
	 */
	public function onBeforeRequest(SoapEvent $event)
	{
		// Calculate a key based on our request.
		$key = sha1($event->getRequest());
		$this->setKey($key);

		// Attempt to locate a response in the cache
		if ($response = $this->cache->get($key)) {
			$event->setResponse($response);
			$event->stopPropagation();
		}
	}

	/**
	 * Caches a response.
	 * @param SoapEvent $event
	 */
	public function onResponseReceived(SoapEvent $event)
	{
		// Store the response
		$this->cache->put($this->key, $event->getResponse(), $this->lifetime);
	}

	/**
	 * You decide what to do when a SoapFault comes back.
	 * @param SoapEvent $event
	 */
	public function onSoapFault(SoapEvent $event)
	{
		// For now, we'll invalidate the cached response.
		$this->cache->put($this->key, null, 1);
	}
}
