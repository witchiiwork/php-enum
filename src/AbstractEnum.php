<?php
declare(strict_types = 1);

namespace W2W\Enum;

use W2W\Enum\Exception\CloneNotSupportedException;
use W2W\Enum\Exception\IllegalArgumentException;
use W2W\Enum\Exception\MismatchException;
use W2W\Enum\Exception\SerializeNotSupportedException;
use W2W\Enum\Exception\UnserializeNotSupportedException;
use ReflectionClass;

abstract class AbstractEnum {
	private $name;
	
	private $ordinal;
	
	private static $values = [];
	
	private static $allValuesLoaded = [];
	
	private static $constants = [];
	
	private function __construct() {
	}
	
	final public static function __callStatic(string $name, array $arguments) : self {
		return static::valueOf($name);
	}
	
	final public static function valueOf(string $name) : self {
		if(isset(self::$values[static::class][$name])) {
			return self::$values[static::class][$name];
		}
		
		$constants = self::constants();
		
		if(array_key_exists($name, $constants)) {
			return self::createValue($name, $constants[$name][0], $constants[$name][1]);
		}
		
		throw new IllegalArgumentException(sprintf("No enum constant %s::%s", static::class, $name));
	}
	
	final private static function createValue(string $name, int $ordinal, array $arguments) : self {
		$instance = new static(...$arguments);
		$instance->name = $name;
		$instance->ordinal = $ordinal;
		
		self::$values[static::class][$name] = $instance;
		
		return $instance;
	}
	
	final public static function values() : array {
		if(isset(self::$allValuesLoaded[static::class])) {
			return self::$values[static::class];
		}
		
		if(!isset(self::$values[static::class])) {
			self::$values[static::class] = [];
		}
		
		foreach(self::constants() as $name => $constant) {
			if(array_key_exists($name, self::$values[static::class])) {
				continue;
			}
			
			static::createValue($name, $constant[0], $constant[1]);
		}
		
		uasort(self::$values[static::class], function (self $a, self $b) {
			return $a->ordinal() <=> $b->ordinal();
		});
		
		self::$allValuesLoaded[static::class] = true;
		
		return self::$values[static::class];
	}
	
	final private static function constants() : array {
		if(isset(self::$constants[static::class])) {
			return self::$constants[static::class];
		}
		
		self::$constants[static::class] = [];
		
		$reflectionClass = new ReflectionClass(static::class);
		$ordinal = -1;
		
		foreach($reflectionClass->getReflectionConstants() as $reflectionConstant) {
			if(!$reflectionConstant->isProtected()) {
				continue;
			}
			
			$value = $reflectionConstant->getValue();
			
			self::$constants[static::class][$reflectionConstant->name] = [++$ordinal, is_array($value) ? $value : []];
		}
		
		return self::$constants[static::class];
	}
	
	final public function name() : string {
		return $this->name;
	}
	
	final public function ordinal() : int {
		return $this->ordinal;
	}
	
	final public function compareTo(self $other) : int {
		if(!$other instanceof static) {
			throw new MismatchException(sprintf("The passed enum %s is not of the same type as %s", get_class($other), static::class));
		}
		
		return $this->ordinal - $other->ordinal;
	}
	
	final public function __clone() {
		throw new CloneNotSupportedException();
	}
	
	final public function __sleep() : void {
		throw new SerializeNotSupportedException();
	}
	
	final public function __wakeup() : void {
		throw new UnserializeNotSupportedException();
	}
	
	public function __toString() : string {
		return $this->name;
	}
}