<?php
/*
 * Desc:
 * User: zhiqiang
 * Date: 2021-10-31 00:51
 */

namespace pkg6\flysystem\github\Tests;

use League\Flysystem\Config;
use Mockery;
use PHPUnit\Framework\TestCase;
use pkg6\flysystem\github\GithubAdapter;

/**
 * Class GithubAdapterTest.
 *
 * @author zhiqiang
 */
class GithubAdapterTest extends TestCase
{
    public function adapter()
    {
        return Mockery::mock(GithubAdapter::class, ['token', 'uername', 'repository'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testWrite()
    {
        $this->assertFalse($this->adapter()->write('foo/bar.md', 'content', new Config()));
    }

    public function testWriteStream()
    {
        $this->assertFalse($this->adapter()->writeStream('foo/bar.md', tmpfile(), new Config()));
    }

    public function testUpdata()
    {
        $this->assertFalse($this->adapter()->update('foo/bar.md', 'content', new Config()));
    }

    public function testUpdateStream()
    {
        $this->assertFalse($this->adapter()->updateStream('foo/bar.md', tmpfile(), new Config()));
    }

    public function testDelete()
    {
        $this->assertFalse($this->adapter()->delete('foo/bar.md'));
    }

    public function testHas()
    {
        $this->assertFalse($this->adapter()->has('foo/bar.md'));
    }

    public function testRead()
    {
        $this->assertFalse($this->adapter()->read('foo/bar.md'));
    }

    public function testlistContents()
    {
        $this->assertIsArray($this->adapter()->listContents());
    }

    public function testGetMetadata()
    {
        $this->assertFalse($this->adapter()->getMetadata('foo/bar.md'));
    }

    public function testGetSize()
    {
        $this->assertFalse($this->adapter()->getSize('foo/bar.md'));
    }

    public function testGetMimetype()
    {
        $this->assertFalse($this->adapter()->getMimetype('foo/bar.md'));
    }

    public function testShow()
    {
        $this->assertFalse($this->adapter()->show('foo/bar.md'));
    }

    public function testGetUrl()
    {
        $this->assertIsString($this->adapter()->getUrl('foo/bar.md'));
    }
}
