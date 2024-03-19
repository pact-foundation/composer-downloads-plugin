<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use Composer\Package\RootPackage;
use Composer\Package\Version\VersionParser;
use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\VersionFilter;
use LastCall\DownloadsPlugin\Model\Version;
use PHPUnit\Framework\MockObject\MockObject;

class VersionFilterTest extends BaseFilterTestCase
{
    private VersionParser|MockObject $versionParser;
    private string $version = '1.2.3.0';
    private string $prettyVersion = 'v1.2.3';

    protected function setUp(): void
    {
        $this->versionParser = $this->createMock(VersionParser::class);
        parent::setUp();
    }

    public function getInvalidVersionTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidVersionTests
     */
    public function testInvalidVersion(mixed $invalidVersion, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->versionParser->expects($this->never())->method('normalize');
        $this->expectUnexpectedValueException('version', sprintf('must be string, "%s" given', $type));
        $this->filter->filter([
            'version' => $invalidVersion,
        ]);
    }

    public function testVersionFromParentPackage(): void
    {
        $this->parent->expects($this->once())->method('getVersion')->willReturn($this->version);
        $this->parent->expects($this->once())->method('getPrettyVersion')->willReturn($this->prettyVersion);
        $this->versionParser->expects($this->never())->method('normalize');
        $this->assertVersion($this->filter->filter([]), $this->version, $this->prettyVersion);
        $this->assertVersion($this->filter->filter(['cached']), $this->version, $this->prettyVersion);
    }

    public function testVersionFromParentRootPackage(): void
    {
        $parent = new RootPackage('vendor/project-name', '1.0.0', 'v1.0.0');
        $filter = new VersionFilter($this->name, $parent, $this->versionParser);
        $this->parent->expects($this->never())->method('getVersion');
        $this->parent->expects($this->never())->method('getPrettyVersion');
        $this->versionParser->expects($this->once())->method('normalize')->with('dev-master')->willReturn('9999999-dev');
        $this->assertVersion($filter->filter([]), '9999999-dev', 'dev-master');
        $this->assertVersion($filter->filter(['cached']), '9999999-dev', 'dev-master');
    }

    public function testCustomVersion(): void
    {
        $this->parent->expects($this->never())->method('getVersion');
        $this->parent->expects($this->never())->method('getPrettyVersion');
        $this->versionParser->expects($this->once())->method('normalize')->with('dev-master')->willReturn('9999999-dev');
        $this->assertVersion($this->filter->filter(['version' => '1.2.3']), '9999999-dev', '1.2.3');
        $this->assertVersion($this->filter->filter([]), '9999999-dev', '1.2.3');
    }

    protected function createFilter(): FilterInterface
    {
        return new VersionFilter($this->name, $this->parent, $this->versionParser);
    }

    private function assertVersion(Version $version, string $expectedVersion, string $expectedPrettyVersion): void
    {
        $this->assertSame($expectedVersion, $version->version);
        $this->assertSame($expectedPrettyVersion, $version->prettyVersion);
    }
}
