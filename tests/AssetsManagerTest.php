<?php

class AssetsManagerTest extends PHPUnit_Framework_TestCase
{
	protected $manager;

	protected function setUp()
	{
		$this->manager = new Stolz\Assets\Manager();
	}

	public function testConfigSetsDirs()
	{
		$config = array(
			'public_dir'   => __DIR__,
			'css_dir'      => uniqid('css'),
			'js_dir'       => uniqid('js'),
			'packages_dir' => uniqid('packages'),
			'pipeline_dir' => uniqid('pipe'),
		);

		$this->manager->config($config);

		foreach($config as $key => $value)
		{
			$this->assertEquals($value, PHPUnit_Framework_Assert::readAttribute($this->manager, $key));
		}
	}

	/**
	 * @expectedException Exception
	 */
	public function testConfigRequirePublicDirWhenPipelineEnabled()
	{
		$this->manager->config(array('pipeline' => true, 'public_dir' => '/dev/null'));
	}

	public function testRemoteLinkDetection()
	{
		$method = self::getMethod('isRemoteLink');

		$this->assertTrue($method->invokeArgs($this->manager, array('http://foo')));
		$this->assertTrue($method->invokeArgs($this->manager, array('https://foo')));
		$this->assertTrue($method->invokeArgs($this->manager, array('//foo')));

		$this->assertFalse($method->invokeArgs($this->manager, array('/')));
		$this->assertFalse($method->invokeArgs($this->manager, array('/foo')));
		$this->assertFalse($method->invokeArgs($this->manager, array('foo')));
	}


	public function testPackageAssetDetection()
	{
		$vendor = '_This-Is-Vendor.0';
		$name = '_This-Is-Package.9';
		$asset = 'local/asset.css';

		$method = self::getMethod('assetIsFromPackage');
		$package = $method->invokeArgs($this->manager, array("$vendor/$name:$asset"));

		$this->assertCount(3, $package);
		$this->assertEquals($vendor, $package[0]);
		$this->assertEquals($name, $package[1]);
		$this->assertEquals($asset, $package[2]);

		$this->assertFalse($method->invokeArgs($this->manager, array('foo')));
		$this->assertFalse($method->invokeArgs($this->manager, array('foo/bar')));
		$this->assertFalse($method->invokeArgs($this->manager, array('foo/bar/foo:bar')));
		$this->assertFalse($method->invokeArgs($this->manager, array('foo:bar')));
	}

	public function testAddOneCss()
	{
		$this->assertCount(0, $this->manager->getCss());

		$asset = uniqid('asset');
		$this->manager->addCss($asset);
		$assets = $this->manager->getCss();

		$this->assertCount(1, $assets);
		$this->assertStringEndsWith($asset, array_pop($assets));
		$this->assertCount(0, $assets);
	}

	public function testAddOneJs()
	{
		$this->assertCount(0, $this->manager->getJs());

		$asset = uniqid('asset');
		$this->manager->addJs($asset);
		$assets = $this->manager->getJs();

		$this->assertCount(1, $assets);
		$this->assertStringEndsWith($asset, array_pop($assets));
		$this->assertCount(0, $assets);
	}

	public function testAddMultipleCss()
	{
		$this->assertCount(0, $this->manager->getCss());

		$asset1 = uniqid('asset1');
		$asset2 = uniqid('asset2');
		$this->manager->addCss(array($asset1, $asset2));
		$assets = $this->manager->getCss();

		$this->assertCount(2, $assets);
		$this->assertStringEndsWith($asset2, array_pop($assets));
		$this->assertStringEndsWith($asset1, array_pop($assets));
		$this->assertCount(0, $assets);
	}

	public function testAddMultipleJs()
	{
		$this->assertCount(0, $this->manager->getJs());

		$asset1 = uniqid('asset1');
		$asset2 = uniqid('asset2');
		$this->manager->addJs(array($asset1, $asset2));
		$assets = $this->manager->getJs();

		$this->assertCount(2, $assets);
		$this->assertStringEndsWith($asset2, array_pop($assets));
		$this->assertStringEndsWith($asset1, array_pop($assets));
		$this->assertCount(0, $assets);
	}

	public function testDetectAndAddCss()
	{
		$this->assertCount(0, $this->manager->getCss());
		$this->assertCount(0, $this->manager->getJs());

		$asset = 'foo.css';
		$this->manager->addCss($asset);

		$this->assertCount(1, $assets = $this->manager->getCss());
		$this->assertCount(0, $this->manager->getJs());
		$this->assertStringEndsWith($asset, array_pop($assets));
	}

	public function testDetectAndAddJs()
	{
		$this->assertCount(0, $this->manager->getCss());
		$this->assertCount(0, $this->manager->getJs());

		$asset = 'foo.js';
		$this->manager->addJs($asset);

		$this->assertCount(1, $assets = $this->manager->getJs());
		$this->assertCount(0, $this->manager->getCss());
		$this->assertStringEndsWith($asset, array_pop($assets));
	}

	public function testDetectAndAddCollection()
	{
		$asset1 = 'foo.js';
		$asset2 = 'foo.css';
		$collection = array($asset1, $asset2);
		$this->manager->config(array('collections' => array('collection' => $collection)));

		$this->assertCount(0, $this->manager->getCss());
		$this->assertCount(0, $this->manager->getJs());

		$this->manager->add('collection');

		$this->assertCount(1, $assets1 = $this->manager->getJs());
		$this->assertCount(1, $assets2 = $this->manager->getCss());

		$this->assertStringEndsWith($asset1, array_pop($assets1));
		$this->assertStringEndsWith($asset2, array_pop($assets2));
	}

	protected static function getMethod($name) {
		$class = new ReflectionClass('Stolz\Assets\Manager');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
}
