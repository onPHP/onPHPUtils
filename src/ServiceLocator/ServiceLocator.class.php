<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\Utils;

	class ServiceLocator implements IServiceLocator
	{
		private $store = array();

		/**
		 * @return \Onphp\Utils\ServiceLocator
		 */
		public static function create()
		{
			return new self;
		}

		/**
		 * @param string $className
		 * @return object
		 */
		public function spawn($className/*,  constructor params ..., ..., ...  */)
		{
			$reflectionClass = new \ReflectionClass($className);
			$constructorArgs = func_get_args();
			array_shift($constructorArgs);

			$object = !empty($constructorArgs)
				? $reflectionClass->newInstanceArgs($constructorArgs)
				: $reflectionClass->newInstance();

			return $this->implementSelf($object);
		}


		/**
		 * @param string $name
		 * @param any $service
		 * @return \Onphp\Utils\ServiceLocator
		 */
		public function set($name, $service)
		{
			\Onphp\Assert::isFalse(
				$this->has($name),
				'object with such name ("'.$name.'") already setted'
			);
			$this->store[$name] = $service;
			return $this;
		}

		/**
		 * @param string $name
		 * @return any
		 */
		public function get($name)
		{
			\Onphp\Assert::isTrue(
				$this->has($name),
				'object with such name ("'.$name.'") was not setted'
			);
			return $this->store[$name];
		}

		/**
		 * @param string $name
		 * @return \Onphp\Utils\ServiceLocator
		 */
		public function drop($name)
		{
			\Onphp\Assert::isTrue($this->has($name), 'object with such name was not setted');
			return $this->store[$name];
		}

		/**
		 * @param string $name
		 * @return boolean
		 */
		public function has($name)
		{
			return array_key_exists($name, $this->store);
		}

		/**
		 * @return array
		 */
		public function getList()
		{
			return $this->store;
		}

		/**
		 * @return \Onphp\Utils\ServiceLocator
		 */
		public function getCopy()
		{
			$subLocator = new $this();
			foreach ($this->store as $key => $value) {
				$subLocator->set($key, $value);
			}
			return $subLocator;
		}

		/**
		 * @param object $object
		 * @return object
		 */
		protected function implementSelf($object)
		{
			if ($object instanceof IServiceLocatorSupport) {
				$object->setServiceLocator($this->getSelf());
			}
			return $object;
		}

		/**
		 * @return \Onphp\Utils\ServiceLocator
		 */
		protected function getSelf()
		{
			return $this->getCopy();
		}
	}
?>