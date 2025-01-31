<?php

use CodeIgniter\Publisher\Exceptions\PublisherException;
use CodeIgniter\Publisher\Publisher;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Publishers\TestPublisher;

class PublisherInputTest extends CIUnitTestCase
{
	/**
	 * A known, valid file
	 *
	 * @var string
	 */
	private $file = SUPPORTPATH . 'Files/baker/banana.php';

	/**
	 * A known, valid directory
	 *
	 * @var string
	 */
	private $directory = SUPPORTPATH . 'Files/able/';

	/**
	 * Initialize the helper, since some
	 * tests call static methods before
	 * the constructor would load it.
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		helper(['filesystem']);
	}

	//--------------------------------------------------------------------

	public function testAddPathFile()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$publisher->addPath('baker/banana.php');

		$this->assertSame([$this->file], $publisher->get());
	}

	public function testAddPathFileRecursiveDoesNothing()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$publisher->addPath('baker/banana.php', true);

		$this->assertSame([$this->file], $publisher->get());
	}

	public function testAddPathDirectory()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
		];

		$publisher->addPath('able');

		$this->assertSame($expected, $publisher->get());
	}

	public function testAddPathDirectoryRecursive()
	{
		$publisher = new Publisher(SUPPORTPATH);

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->addPath('Files');

		$this->assertSame($expected, $publisher->get());
	}

	public function testAddPaths()
	{
		$publisher = new Publisher(SUPPORTPATH . 'Files');

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
		];

		$publisher->addPaths([
			'able',
			'baker/banana.php',
		]);

		$this->assertSame($expected, $publisher->get());
	}

	public function testAddPathsRecursive()
	{
		$publisher = new Publisher(SUPPORTPATH);

		$expected = [
			$this->directory . 'apple.php',
			$this->directory . 'fig_3.php',
			$this->directory . 'prune_ripe.php',
			SUPPORTPATH . 'Files/baker/banana.php',
			SUPPORTPATH . 'Log/Handlers/TestHandler.php',
		];

		$publisher->addPaths([
			'Files',
			'Log',
		], true);

		$this->assertSame($expected, $publisher->get());
	}

	//--------------------------------------------------------------------

	public function testAddUri()
	{
		$publisher = new Publisher();
		$publisher->addUri('https://raw.githubusercontent.com/codeigniter4/CodeIgniter4/develop/composer.json');

		$scratch = $this->getPrivateProperty($publisher, 'scratch');

		$this->assertSame([$scratch . 'composer.json'], $publisher->get());
	}

	public function testAddUris()
	{
		$publisher = new Publisher();
		$publisher->addUris([
			'https://raw.githubusercontent.com/codeigniter4/CodeIgniter4/develop/LICENSE',
			'https://raw.githubusercontent.com/codeigniter4/CodeIgniter4/develop/composer.json',
		]);

		$scratch = $this->getPrivateProperty($publisher, 'scratch');

		$this->assertSame([$scratch . 'LICENSE', $scratch . 'composer.json'], $publisher->get());
	}
}
