<?php

namespace tests\Http\Message\MultipartStream;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use Zend\Diactoros\Stream;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class FunctionTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportStreams()
    {
        $body = 'stream contents';

        $builder = new MultipartStreamBuilder();
        $builder->addResource('foobar', $this->createStream($body));

        $multipartStream = (string) $builder->build();
        $this->assertTrue(false !== strpos($multipartStream, $body));
    }

    public function testSupportResources()
    {
        $resource = fopen(__DIR__.'/Resources/httplug.png', 'r');

        $builder = new MultipartStreamBuilder();
        $builder->addResource('image', $resource);

        $multipartStream = (string) $builder->build();
        $this->assertTrue(false !== strpos($multipartStream, 'Content-Disposition: form-data; name="image"; filename="httplug.png"'));
        $this->assertTrue(false !== strpos($multipartStream, 'Content-Type: image/png'));
    }

    public function testSupportURIResources()
    {
        $url = 'https://raw.githubusercontent.com/php-http/multipart-stream-builder/master/tests/Resources/httplug.png';
        $resource = fopen($url, 'r');

        $builder = new MultipartStreamBuilder();
        $builder->addResource('image', $resource);
        $multipartStream = (string) $builder->build();

        $this->assertTrue(false !== strpos($multipartStream, 'Content-Disposition: form-data; name="image"; filename="httplug.png"'));
        $this->assertTrue(false !== strpos($multipartStream, 'Content-Type: image/png'));

        $urlContents = file_get_contents($url);
        $this->assertContains($urlContents, $multipartStream);
    }

    public function testResourceFilenameIsNotLocaleAware()
    {
        // Get current locale
        $originalLocale = setlocale(LC_ALL, "0");

        // Set locale to something strange.
        setlocale(LC_ALL, 'C');

        $resource = fopen(__DIR__.'/Resources/httplug.png', 'r');
        $builder = new MultipartStreamBuilder();
        $builder->addResource('image', $resource, ['filename'=> 'äa.png']);

        $multipartStream = (string) $builder->build();
        $this->assertTrue(0 < preg_match('|filename="([^"]*?)"|si', $multipartStream, $matches), 'Could not find any filename in output.');
        $this->assertEquals('äa.png', $matches[1]);

        // Reset the locale
        setlocale(LC_ALL, $originalLocale);
    }

    public function testHeaders()
    {
        $builder = new MultipartStreamBuilder();
        $builder->addResource('foobar', 'stream contents', ['headers' => ['Content-Type' => 'html/image', 'content-length' => '4711', 'CONTENT-DISPOSITION' => 'none']]);

        $multipartStream = (string) $builder->build();
        $this->assertTrue(false !== strpos($multipartStream, 'Content-Type: html/image'));
        $this->assertTrue(false !== strpos($multipartStream, 'content-length: 4711'));
        $this->assertTrue(false !== strpos($multipartStream, 'CONTENT-DISPOSITION: none'));

        // Make sure we do not add extra headers with a different case
        $this->assertTrue(false === strpos($multipartStream, 'Content-Disposition:'));
    }

    public function testContentLength()
    {
        $builder = new MultipartStreamBuilder();
        $builder->addResource('foobar', 'stream contents');

        $multipartStream = (string) $builder->build();
        $this->assertTrue(false !== strpos($multipartStream, 'Content-Length: 15'));
    }

    public function testFormName()
    {
        $builder = new MultipartStreamBuilder();
        $builder->addResource('a-formname', 'string');

        $multipartStream = (string) $builder->build();
        $this->assertTrue(false !== strpos($multipartStream, 'Content-Disposition: form-data; name="a-formname"'));
    }

    public function testAddResourceWithSameName()
    {
        $builder = new MultipartStreamBuilder();
        $builder->addResource('name', 'foo1234567890foo');
        $builder->addResource('name', 'bar1234567890bar');

        $multipartStream = (string) $builder->build();
        $this->assertTrue(false !== strpos($multipartStream, 'bar1234567890bar'));
        $this->assertTrue(false !== strpos($multipartStream, 'foo1234567890foo'), 'Using same name must not overwrite');
    }

    public function testBoundary()
    {
        $boundary = 'SpecialBoundary';
        $builder = new MultipartStreamBuilder();
        $builder->addResource('content0', 'string');
        $builder->setBoundary($boundary);

        $multipartStream = (string) $builder->build();
        $this->assertEquals(2, substr_count($multipartStream, $boundary));

        $builder->addResource('content1', 'string');
        $builder->addResource('content2', 'string');
        $builder->addResource('content3', 'string');

        $multipartStream = (string) $builder->build();
        $this->assertEquals(5, substr_count($multipartStream, $boundary));
    }

    public function testReset()
    {
        $boundary = 'SpecialBoundary';
        $builder = new MultipartStreamBuilder();
        $builder->addResource('content0', 'foobar');
        $builder->setBoundary($boundary);

        $builder->reset();
        $multipartStream = (string) $builder->build();
        $this->assertNotContains('foobar', $multipartStream, 'Stream should not have any data after reset()');
        $this->assertNotEquals($boundary, $builder->getBoundary(), 'Stream should have a new boundary after reset()');
        $this->assertNotEmpty($builder->getBoundary());
    }

    /**
     * @param string $body
     *
     * @return Stream
     */
    private function createStream($body)
    {
        $stream = new Stream('php://memory', 'rw');
        $stream->write($body);
        $stream->rewind();

        return $stream;
    }
}
