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
}
