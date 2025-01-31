<?php

use CodeIgniter\Publisher\Exceptions\PublisherException;
use CodeIgniter\Publisher\Publisher;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Publisher Restrictions Test
 *
 * Tests that the restrictions defined in the configuration
 * file properly prevent disallowed actions.
 */
class PublisherRestrictionsTest extends CIUnitTestCase
{
	/**
	 * @see Tests\Support\Config\Registrars::Publisher()
	 */
	public function testRegistrarsNotAllowed()
	{
		$this->assertArrayNotHasKey(SUPPORTPATH, config('Publisher')->restrictions);
	}

	public function testImmutableRestrictions()
	{
		$publisher = new Publisher();

		// Try to "hack" the Publisher by adding our desired destination to the config
		config('Publisher')->restrictions[SUPPORTPATH] = '*';

		$restrictions = $this->getPrivateProperty($publisher, 'restrictions');

		$this->assertArrayNotHasKey(SUPPORTPATH, $restrictions);
	}

	/**
	 * @dataProvider fileProvider
	 */
	public function testDefaultPublicRestrictions(string $path)
	{
		$publisher = new Publisher(ROOTPATH, FCPATH);
		$pattern   = config('Publisher')->restrictions[FCPATH];

		// Use the scratch space to create a file
		$file = $publisher->getScratch() . $path;
		file_put_contents($file, 'To infinity and beyond!');

		$result = $publisher->addFile($file)->merge();
		$this->assertFalse($result);

		$errors = $publisher->getErrors();
		$this->assertCount(1, $errors);
		$this->assertSame([$file], array_keys($errors));

		$expected = lang('Publisher.fileNotAllowed', [$file, FCPATH, $pattern]);
		$this->assertSame($expected, $errors[$file]->getMessage());
	}

	public function fileProvider()
	{
		yield 'php'  => ['index.php'];
		yield 'exe'  => ['cat.exe'];
		yield 'flat' => ['banana'];
	}

	/**
	 * @dataProvider destinationProvider
	 */
	public function testDestinations(string $destination, bool $allowed)
	{
		config('Publisher')->restrictions = [
			APPPATH                   => '',
			FCPATH                    => '',
			SUPPORTPATH . 'Files'     => '',
			SUPPORTPATH . 'Files/../' => '',
		];

		if (! $allowed)
		{
			$this->expectException(PublisherException::class);
			$this->expectExceptionMessage(lang('Publisher.destinationNotAllowed', [$destination]));
		}

		$publisher = new Publisher(null, $destination);
		$this->assertInstanceOf(Publisher::class, $publisher);
	}

	public function destinationProvider()
	{
		return [
			'explicit' => [
				APPPATH,
				true,
			],
			'subdirectory' => [
				APPPATH . 'Config',
				true,
			],
			'relative' => [
				SUPPORTPATH . 'Files/able/../',
				true,
			],
			'parent' => [
				SUPPORTPATH,
				false,
			],
		];
	}
}
