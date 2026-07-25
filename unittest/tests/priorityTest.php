<?php

declare(strict_types=1);

use orange\priority\Priority;

final class priorityTest extends \unitTestHelper
{
    protected function setUp(): void
    {
        $this->instance = new Priority([]);
    }

    public function testEmpty(): void
    {
        $this->assertSame([], $this->instance->array());
        $this->assertSame('', $this->instance->text());
    }

    public function testOrderedByPriority(): void
    {
        $this->instance->addLast('l');
        $this->instance->addNormal('n');
        $this->instance->addFirst('f');

        $this->assertSame(['f', 'n', 'l'], $this->instance->array());
    }

    /**
     * The whole point of the weight buckets: values sharing a priority come
     * back in the order they were added, and none of them go missing.
     */
    public function testInsertionOrderIsKeptWithinAPriority(): void
    {
        foreach (range(1, 25) as $i) {
            $this->instance->addNormal('n' . $i);
        }

        $expected = array_map(fn($i) => 'n' . $i, range(1, 25));

        $this->assertSame($expected, $this->instance->array());
    }

    public function testPrioritiesInterleaveButKeepTheirOwnOrder(): void
    {
        $this->instance->addNormal('n1');
        $this->instance->addFirst('f1');
        $this->instance->addNormal('n2');
        $this->instance->addLast('l1');
        $this->instance->addFirst('f2');
        $this->instance->addNormal('n3');

        $this->assertSame(['f1', 'f2', 'n1', 'n2', 'n3', 'l1'], $this->instance->array());
        $this->assertSame('f1f2n1n2n3l1', $this->instance->text());
    }

    public function testAddDefaultsToTheMiddle(): void
    {
        $this->instance->addVeryfirst('first');
        $this->instance->add('middle');
        $this->instance->addVerylast('last');

        $this->assertSame(['first', 'middle', 'last'], $this->instance->array());
    }

    public function testAddAcceptsAnArrayOfValues(): void
    {
        $this->instance->addNormal(['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $this->instance->array());
    }

    public function testUnknownPriorityThrows(): void
    {
        $this->expectException(\Exception::class);

        $this->instance->addNoSuchPriority('x');
    }

    public function testNoDuplicates(): void
    {
        $priority = new Priority(['no duplicates' => true]);

        $priority->addNormal('a');
        $priority->addNormal('a');
        $priority->addFirst('b');
        $priority->addNormal('c');

        $this->assertSame(['b', 'a', 'c'], $priority->array());
    }

    public function testDuplicatesAreKeptByDefault(): void
    {
        $this->instance->addNormal('a');
        $this->instance->addNormal('a');

        $this->assertSame(['a', 'a'], $this->instance->array());
    }

    public function testJson(): void
    {
        $this->instance->addFirst('b');
        $this->instance->addNormal('a');

        $this->assertSame('["b","a"]', $this->instance->json());
    }

    public function testPrioritiesFromCommaSeparatedString(): void
    {
        $priority = new Priority(['priorities' => 'top,middle,bottom']);

        $priority->addBottom('c');
        $priority->addTop('a');
        $priority->addMiddle('b');

        $this->assertSame(['a', 'b', 'c'], $priority->array());
    }

    public function testAddingAfterReadingResortsTheList(): void
    {
        $this->instance->addNormal('n');

        $this->assertSame(['n'], $this->instance->array());

        $this->instance->addFirst('f');

        $this->assertSame(['f', 'n'], $this->instance->array());
    }
}
