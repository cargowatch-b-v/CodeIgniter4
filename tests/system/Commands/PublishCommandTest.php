<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Tests\Support\Publishers\TestPublisher;

final class PublishCommandTest extends CIUnitTestCase
{
	private $streamFilter;

	protected function setUp(): void
	{
		parent::setUp();
		CITestStreamFilter::$buffer = '';

		$this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
		$this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		stream_filter_remove($this->streamFilter);
		TestPublisher::setResult(true);
	}

	public function testDefault()
	{
		command('publish');

		$this->assertStringContainsString(lang('Publisher.publishSuccess', [
			TestPublisher::class,
			0,
			WRITEPATH,
		]), CITestStreamFilter::$buffer);
	}

	public function testFailure()
	{
		TestPublisher::setResult(false);

		command('publish');

		$this->assertStringContainsString(lang('Publisher.publishFailure', [
			TestPublisher::class,
			WRITEPATH,
		]), CITestStreamFilter::$buffer);
	}
}
