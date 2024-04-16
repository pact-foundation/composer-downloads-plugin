<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Attribute\Validator\VariablesValidator;
use LastCall\DownloadsPlugin\Enum\Attribute;

class VariablesValidatorTest extends AbstractValidatorTestCase
{
    public function getEmptyVariablesTests(): array
    {
        return [
            [null],
            [[]],
        ];
    }

    /**
     * @dataProvider getEmptyVariablesTests
     */
    public function testEmptyVariables(mixed $values): void
    {
        $this->assertSame([
            '{$id}' => $this->id,
            '{$version}' => '',
        ], $this->validator->validate($values));
    }

    public function getInvalidVariablesTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidVariablesTests
     */
    public function testInvalidVariables(mixed $invalidVariables, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('must be array, "%s" given', $type));
        $this->validator->validate($invalidVariables);
    }

    public function getInvalidVariableKeyTests(): array
    {
        return [
            ['baz'],
            ['$baz'],
            ['{baz}'],
            ['${baz}'],
            ['{$baz'],
            ['$baz}'],
        ];
    }

    /**
     * @dataProvider getInvalidVariableKeyTests
     */
    public function testInvalidVariableKey(string $invalidVariableKey): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('is invalid: Variable key "%s" should be this format "{$variable-name}"', $invalidVariableKey));
        $this->validator->validate([
            $invalidVariableKey => '"baz"',
        ]);
    }

    public function getInvalidVariableValueTests(): array
    {
        return [
            ["{ foo: 'bar' }", 'stdClass'],
            ["['foo', 'baz']", 'array'],
            ['true', 'bool'],
            ['false', 'bool'],
            ['null', 'null'],
            ['123', 'int'],
            ['1.92', 'float'],
            ['1e-2', 'float'],
        ];
    }

    /**
     * @dataProvider getInvalidVariableValueTests
     */
    public function testInvalidVariableValue(string $invalidVariableValue, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('is invalid: Expression "%s" should be evaluated to string, "%s" given', $invalidVariableValue, $type));
        $this->validator->validate([
            '{$baz}' => $invalidVariableValue,
        ]);
    }

    public function getInvalidVariableExpressionSyntaxTests(): array
    {
        return [
            ['in_array(1, range(1, 10)', 'expected closing `)`'],
            ["'foo' in ['foo', 'baz']", 'unexpected end of string'],
            ["invalid('test')", 'var `invalid` not defined'],
            ["PHP_OS('test')", '`PHP_OS` is not callable'],
        ];
    }

    /**
     * @dataProvider getInvalidVariableExpressionSyntaxTests
     */
    public function testInvalidVariableExpressionSyntax(string $expression, string $reason): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('variables', sprintf('is invalid. There is an error while evaluating expression "%s": %s', $expression, $reason));
        $this->validator->validate([
            '{$baz}' => $expression,
        ]);
    }

    public function testValidateVariables(): void
    {
        $expectedVariables = [
            '{$id}' => $this->id,
            '{$version}' => '1.2.3',
            '{$foo}' => 'foo',
            '{$baz}' => 'baz3',
        ];
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VERSION)->willReturn('1.2.3');
        $this->assertEquals($expectedVariables, $this->validator->validate([
            '{$foo}' => '"foo"',
            '{$baz}' => '"baz"~1+2',
        ]));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new VariablesValidator($this->id, $this->parent, $this->attributeManager);
    }
}
