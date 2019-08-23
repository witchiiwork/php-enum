<?php
declare(strict_types = 1);

namespace W2W\Enum;

use W2W\Enum\Exception\CloneNotSupportedException;
use W2W\Enum\Exception\SerializeNotSupportedException;
use W2W\Enum\Exception\UnserializeNotSupportedException;

final class NullValue {
	private static $instance;
	
	private function __construct() {
    }
	
	public static function instance() : self {
		return self::$instance ? : self::$instance = new self();
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
}