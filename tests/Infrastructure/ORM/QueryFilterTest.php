<?php

namespace Unzer\Core\Tests\Infrastructure\ORM;

use DateTime;
use Exception;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryFilterTest
 *
 * @package Unzer\Core\Tests\Infrastructure\ORM
 */
class QueryFilterTest extends TestCase
{
    private static array $validConditions = [
        [
            'chain' => 'and',
            'column' => 'a',
            'operator' => 'like',
            'value' => '%test%',
        ],
        [
            'chain' => 'and',
            'column' => 'a',
            'operator' => '!=',
            'value' => 'test',
        ],
        [
            'chain' => 'or',
            'column' => 'b',
            'operator' => '>',
            'value' => 123,
        ],
        [
            'chain' => 'or',
            'column' => 'c',
            'operator' => 'IN',
            'value' => [1, 2, 3],
        ],
        [
            'chain' => 'and',
            'column' => 'c',
            'operator' => 'not in',
            'value' => [4, 5, 6],
        ],
        [
            'chain' => 'or',
            'column' => 'd',
            'operator' => 'is null',
            'value' => null,
        ],
        [
            'chain' => 'and',
            'column' => 'e',
            'operator' => 'is not null',
            'value' => null,
        ],
    ];

    /**
     * @return void
     */
    public function testSetLimitOffset()
    {
        $queryFilter = new QueryFilter();
        $queryFilter->setLimit(123);
        $queryFilter->setOffset(10);

        $this->assertEquals(123, $queryFilter->getLimit());
        $this->assertEquals(10, $queryFilter->getOffset());
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testOrderBy()
    {
        $queryFilter = new QueryFilter();
        $queryFilter->orderBy('a');

        $this->assertEquals('a', $queryFilter->getOrderByColumn());
        $this->assertEquals('ASC', $queryFilter->getOrderDirection());

        $queryFilter->orderBy('b', 'ASC');

        $this->assertEquals('b', $queryFilter->getOrderByColumn());
        $this->assertEquals('ASC', $queryFilter->getOrderDirection());

        $queryFilter->orderBy('c', 'DESC');

        $this->assertEquals('c', $queryFilter->getOrderByColumn());
        $this->assertEquals('DESC', $queryFilter->getOrderDirection());
    }

    /**
     * @return void
     * @throws QueryFilterInvalidParamException
     */
    public function testQueryFilterChaining()
    {
        $queryFilter = new QueryFilter();

        $a = $queryFilter->setLimit(123);
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->setOffset(123);
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->orderBy('a', 'ASC');
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->where('a', '=', 'ASC');
        $this->assertEquals($queryFilter, $a);

        $a = $queryFilter->orWhere('a', '=', 'ASC');
        $this->assertEquals($queryFilter, $a);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testQueryFilterCondition()
    {
        $queryFilter = new QueryFilter();
        foreach (self::$validConditions as $condition) {
            if ($condition['chain'] === 'and') {
                $queryFilter->where($condition['column'], $condition['operator'], $condition['value']);
            } else {
                $queryFilter->orWhere($condition['column'], $condition['operator'], $condition['value']);
            }
        }

        $queryConditions = $queryFilter->getConditions();
        $count = count(self::$validConditions);
        $this->assertCount($count, $queryConditions);
        for ($i = 0; $i < $count; $i++) {
            $b = $queryConditions[$i];
            $this->assertInstanceOf('\Unzer\Core\Infrastructure\ORM\QueryFilter\QueryCondition', $b);

            $a = self::$validConditions[$i];
            $this->assertEquals(strtoupper($a['chain']), $b->getChainOperator());
            $this->assertEquals($a['column'], $b->getColumn());
            $this->assertEquals($a['operator'], $b->getOperator());
            $this->assertEquals($a['value'], $b->getValue());
        }
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testOrderByWrongColumn()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->orderBy('123', '123');
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testOrderByWrongDirection()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->orderBy('a', 123);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testConditionWrongTypeValue()
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->where('a', '=', new \stdClass());
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     *
     * @dataProvider wrongConditionProvider
     */
    public function testWrongCondition($column, $operator, $value)
    {
        $this->expectException(QueryFilterInvalidParamException::class);

        $queryFilter = new QueryFilter();
        $queryFilter->where($column, $operator, $value);
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function wrongConditionProvider(): array
    {
        return [
            [
                'column' => 'a',
                'operator' => 'like',
                'value' => 123,
            ],
            [
                'column' => 'a',
                'operator' => 'like',
                'value' => new DateTime(),
            ],
            [
                'column' => 'a',
                'operator' => 'in',
                'value' => new DateTime(),
            ],
            [
                'column' => 'a',
                'operator' => 'not in',
                'value' => 123,
            ],
            [
                'column' => 'a',
                'operator' => '>',
                'value' => true,
            ],
            [
                'column' => 456,
                'operator' => '>',
                'value' => true,
            ],
            [
                'column' => 'a',
                'operator' => 'bla',
                'value' => true,
            ],
        ];
    }
}
