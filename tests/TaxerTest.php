<?php
use app\Taxer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxerTest extends TestCase
{
    private Taxer $taxer;
    private MockObject|Taxer $taxerMock;

    protected function setUp(): void
    {
        $this->taxer = new Taxer();
        $this->taxerMock = $this->getMockBuilder(Taxer::class)
            ->onlyMethods(['getCardData', 'getExchangeRate', 'calculateAmount', 'isEu'])
            ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testProcessRow()
    {
        $row = '{"bin":"123456","amount":"100.00","currency":"USD"}';

        $mockCardData = (object)[
            'country' => (object)['alpha2' => 'US']
        ];
        $mockRate = 1.1;

        $this->taxerMock->expects($this->once())
            ->method('getCardData')
            ->with('123456')
            ->willReturn($mockCardData);

        $this->taxerMock->expects($this->once())
            ->method('getExchangeRate')
            ->with('USD')
            ->willReturn($mockRate);

        $this->taxerMock->expects($this->once())
            ->method('isEu')
            ->with('US')
            ->willReturn(false);

        $this->taxerMock->expects($this->once())
            ->method('calculateAmount')
            ->with(100, 'USD', 1.1, false)
            ->willReturn(110.0);

        $result = $this->taxerMock->processRow($row);

        $this->assertEquals(110.0, $result);
    }

    public function testCalculateAmount()
    {
        $result = $this->taxer->calculateAmount(100, 'EUR', 1, true);
        $this->assertEquals(100 * 0.01, $result);

        $result = $this->taxer->calculateAmount(100, 'EUR', 1.2, false);
        $this->assertEquals(100 / 1.2 * 0.02, $result);

        $result = $this->taxer->calculateAmount(100, 'USD', 1.2, true);
        $this->assertEquals((100 / 1.2) * 0.01, $result);

        $result = $this->taxer->calculateAmount(100, 'USD', 1.2, false);
        $this->assertEquals((100 / 1.2) * 0.02, $result);
    }

    public function testIsEuWithEuCountry()
    {
        $this->assertTrue($this->taxer->isEu('AT'));
        $this->assertTrue($this->taxer->isEu('DE'));
        $this->assertTrue($this->taxer->isEu('FR'));

        $this->assertFalse($this->taxer->isEu('US'));
        $this->assertFalse($this->taxer->isEu('GB'));
        $this->assertFalse($this->taxer->isEu('CN'));
    }
}
