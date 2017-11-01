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

	public function testPrependOneCss()
    {
		$this->assertCount(0, $this->manager->getCss());

		$asset1 = uniqid('asset1');
		$asset2 = uniqid('asset2');
		$this->manager->addCss($asset2);
		$this->manager->prependCss($asset1);

		$assets = $this->manager->getCss();
		$this->assertStringEndsWith($asset2, array_pop($assets));
		$this->assertStringEndsWith($asset1, array_pop($assets));
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

	public function testPrependOneJs()
	{
		$this->assertCount(0, $this->manager->getJs());

		$asset1 = uniqid('asset1');
		$asset2 = uniqid('asset2');
		$this->manager->addJs($asset2);
		$this->manager->prependJs($asset1);

		$assets = $this->manager->getJs();
		$this->assertStringEndsWith($asset2, array_pop($assets));
		$this->assertStringEndsWith($asset1, array_pop($assets));
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

	public function testPrependMultipleCss()
	{
		$this->assertCount(0, $this->manager->getCss());

		$asset1 = uniqid('asset1');
		$asset2 = uniqid('asset2');
		$asset3 = uniqid('asset3');
		$this->manager->addCss($asset3);
		$this->manager->prependCss(array($asset1, $asset2));
		$assets = $this->manager->getCss();

		$this->assertCount(3, $assets);
		$this->assertStringEndsWith($asset3, array_pop($assets));
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

	public function testPrependMultipleJs()
	{
		$this->assertCount(0, $this->manager->getJs());

		$asset1 = uniqid('asset1');
		$asset2 = uniqid('asset2');
		$asset3 = uniqid('asset3');
		$this->manager->addJs($asset3);
		$this->manager->prependJs(array($asset1, $asset2));
		$assets = $this->manager->getJs();

		$this->assertCount(3, $assets);
		$this->assertStringEndsWith($asset3, array_pop($assets));
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

	public function testRegexOptions()
	{
		$files = array(
			'.css',        // Not an asset
			'foo.CSS',
			'foomin.css',
			'foo.min.css', // Skip from minification
			'foo-MIN.css', // Skip from minification

			'.js',        // Not an asset
			'foo.JS',
			'foomin.js',
			'foo.min.js', // Skip from minification
			'foo-MIN.js', // Skip from minification
		);

		// Test asset detection
		$regex = PHPUnit_Framework_Assert::readAttribute($this->manager, 'asset_regex');
		$matching = array_filter($files, function ($file) use ($regex) {
			return 1 === preg_match($regex, $file);
		});
		$this->assertEquals(8, count($matching));

		// Test CSS asset detection
		$regex = PHPUnit_Framework_Assert::readAttribute($this->manager, 'css_regex');
		$matching = array_filter($files, function ($file) use ($regex) {
			return 1 === preg_match($regex, $file);
		});
		$this->assertEquals(4, count($matching));

		// Test JS asset detection
		$regex = PHPUnit_Framework_Assert::readAttribute($this->manager, 'js_regex');
		$matching = array_filter($files, function ($file) use ($regex) {
			return 1 === preg_match($regex, $file);
		});
		$this->assertEquals(4, count($matching));

		// Test minification skip detection
		$regex = PHPUnit_Framework_Assert::readAttribute($this->manager, 'no_minification_regex');
		$matching = array_filter($files, function ($file) use ($regex) {
			return 1 === preg_match($regex, $file);
		});
		$this->assertEquals(4, count($matching));
	}

	protected static function getMethod($name)
	{
		$class = new ReflectionClass('Stolz\Assets\Manager');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
}
