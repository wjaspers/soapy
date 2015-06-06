<?php

namespace TestsAlwaysIncluded\Soapy;

class RequestContext
{
	/** @var array */
	protected $items = array();

	/** @var boolean */
	protected $locked = false;

	/**
	 * @param array $items
	 */
	public function __construct($items = array())
	{
		$this->items = $items;
	}

	/**
	 * Returns the list of options.
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Locks the request context.
	 * This is useful for identifying when a request has actually been sent.
	 */
	public function lock()
	{
		$this->locked = true;
	}

	/**
	 * Returns true if this context is locked, or false if it is writable.
	 * @return boolean
	 */
	public function locked()
	{
		return $this->locked;
	}

	/**
	 * Returns a context argument, if provided.
	 * @return mixed
	 */
	public function __get($parameter)
	{
		return array_key_exists($parameter, $this->items) ? $this->items[$parameter] : null;
	}

	/**
	 * Overrides a context parameter.
	 * @param string $parameter	The context parameter to change.
	 * @param mixed $value		The value of the parameter.
	 * @throws RuntimeException	If the context has been locked.
	 */
	public function __set($parameter, $value)
	{
		if ($this->locked) {
			throw new RuntimeException('Attempted to alter the RequestContext after it has been locked.');
		}

		$this->items[$parameter] = $value;
	}
}
