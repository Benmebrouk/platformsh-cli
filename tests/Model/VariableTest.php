<?php

namespace Platformsh\Cli\Tests\Model;

use Platformsh\Cli\Model\Variable;

class VariableTest extends \PHPUnit_Framework_TestCase
{
    private $invalidMessage = 'Variables must be defined as type:name=value';

    public function testParseValidVariables()
    {
        $this->assertEquals(
            ['env', 'foo', 'bar'],
            (new Variable())->parse('env:foo=bar')
        );

        $this->assertEquals(
            ['env', 'foo:oof', 'bar'],
            (new Variable())->parse('env:foo:oof=bar')
        );

        $this->assertEquals(
            ['env', 'foo.123', 'bar'],
            (new Variable())->parse('env:foo.123=bar')
        );

        $this->assertEquals(
            ['complex', 'json', '{"foo":"bar"}'],
            (new Variable())->parse('complex:json={"foo":"bar"}')
        );

        $this->assertEquals(
            ['empty', 'value', ''],
            (new Variable())->parse('empty:value=')
        );
    }

    public function testParseInvalidVariableType()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid variable type');
        (new Variable())->parse('a/b:c=d');
    }


    public function testParseInvalidVariableName()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Invalid variable name');
        (new Variable())->parse('a:b(c)=d');
    }

    public function testParseVariableWithNoDelimiter() {
        $this->setExpectedException(\InvalidArgumentException::class, $this->invalidMessage);
        (new Variable())->parse('foo');
    }

    public function testParseVariableWithWrongDelimiterOrder() {
        $this->setExpectedException(\InvalidArgumentException::class, $this->invalidMessage);
        (new Variable())->parse('a=b:c');
    }

    public function testParseVariableWithEmptyType() {
        $this->setExpectedException(\InvalidArgumentException::class, $this->invalidMessage);
        (new Variable())->parse(':b=c');
    }
}
