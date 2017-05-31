<?php
namespace SerialImprovement\Dql;

use PHPUnit\Framework\TestCase;

class DqlTest extends TestCase
{
    public function testInterpret()
    {
        $dql = new Dql();

        $dql->addPattern('SET', function (array $args) {
            return '';
        });

        $dql->addPattern('PREPEND [str:prefix]', function (array $args) {
            return $args['prefix'] . $args['$prev'];
        });

        $dql->addPattern('APPEND [str:suffix]', function (array $args) {
            return $args['$prev'] . $args['suffix'];
        });

        $out = $dql->interpret('SET APPEND "Bill Nunney" THEN PREPEND "Mr. " THEN APPEND " Esq."');

        $this->assertSame('Mr. Bill Nunney Esq.', $out);
    }

    public function testInterpret2()
    {
        $dql = new Dql();

        $dql->addPattern('SET [int:num]', function (array $args) {
            return $args['num'];
        });

        $dql->addPattern('ADD [int:num]', function (array $args) {
            return $args['num'] + $args['$prev'];
        });

        $dql->addPattern('SUB [int:num]', function (array $args) {
            return $args['$prev'] - $args['num'];
        });

        $dql->addPattern('MUL [int:num]', function (array $args) {
            return $args['$prev'] * $args['num'];
        });

        $out = $dql->interpret('SET 0 THEN ADD 1 THEN MUL 5 THEN ADD 2 THEN SUB 1');

        $this->assertSame(6, $out);
    }
}
