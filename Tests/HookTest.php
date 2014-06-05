<?php
namespace Asgard\Hook\Tests;

use \Asgard\Hook\HooksManager;
use \Asgard\Hook\HookChain;
use \Asgard\Hook\Hookable;
use \Asgard\Hook\HooksContainer;

class HookTest extends \PHPUnit_Framework_TestCase {
	public function testHook() {
		$hooks = new HooksManager;

		$hooks->hook('test', function() {
			return 'hello';
		});
		$this->assertEquals('hello', $hooks->trigger('test'));

		$hooks->hookBefore('test', function() {
			return 'yo';
		});
		$this->assertEquals('yo', $hooks->trigger('test'));

		$mock = $this->getMock('StdClass', array('on', 'after'));
		$mock->expects($this->once())->method('on');
		$mock->expects($this->once())->method('after');
		$hooks->hook('foo', array($mock, 'on'));
		$hooks->hookAfter('foo', array($mock, 'after'));
		$hooks->trigger('foo');

		$this->assertCount(2, $hooks->get('foo'));
	}

	public function testHooks() {
		$hooks = new HooksManager;

		$mock = $this->getMock('StdClass', array('on', 'after'));
		$mock->expects($this->once())->method('on');
		$mock->expects($this->once())->method('after');

		$hooks->hooks(array(
			'foo' => array(
				array($mock, 'on'),
				array($mock, 'after'),
			)
		));
		$hooks->trigger('foo');
	}

	public function testExecuted() {
		$hooks = new HooksManager;

		$hooks->hooks(array(
			'foo' => array(
				function($chain) { },
				function() { },
			)
		));
		$chain = new HookChain;
		$hooks->triggerChain($chain, 'foo');
		$this->assertEquals(2, $chain->executed);
	}

	public function testChainStop() {
		$hooks = new HooksManager;

		$hooks->hooks(array(
			'foo' => array(
				function($chain) { $chain->stop(); },
				function() { },
			)
		));
		$chain = new HookChain;
		$hooks->triggerChain($chain, 'foo');
		$this->assertEquals(1, $chain->executed);
	}

	public function testHooksContainer() {
		$hooks = new HooksManager();

		\Asgard\Core\App::instance()['cache'] = new \Asgard\Cache\NullCache();

		$fhooks = \Asgard\Hook\Tests\Fixtures\Hooks::fetchHooks();

		$hooks->hooks($fhooks);

		$this->assertEquals('bar', $hooks->trigger('test'));
	}

	public function testHookable() {
		$foo = new Foo;
		$foo->hook('foo', function() { return 'plplp'; });
		$this->assertEquals('plplp', $foo->trigger('foo'));
	}
}

class Foo extends \Asgard\Hook\Hookable {}