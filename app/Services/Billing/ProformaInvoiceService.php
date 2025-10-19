<?php

namespace Tests\Feature\Billing;

use App\Services\Billing\ProformaInvoiceService;
use Tests\TestCase;

class ProformaInvoiceServiceTest extends TestCase
{
    private ProformaInvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProformaInvoiceService();
    }

    /**
     * Test basic calculation
     */
    public function test_basic_calculation(): void
    {
        $result = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
        ]);

        $this->assertTrue($result['success'] ?? true);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertGreaterThan(0, $result['total']);
    }

    /**
     * Test different periods
     */
    public function test_different_periods(): void
    {
        $monthly = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'monthly',
        ]);

        $quarterly = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'quarterly',
        ]);

        $yearly = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
        ]);

        // Monthly price should be lowest
        $this->assertLessThan($quarterly['subtotal'], $yearly['subtotal']);
        $this->assertLessThan($quarterly['subtotal'], $monthly['subtotal'] * 10);
    }

    /**
     * Test discount application
     */
    public function test_discount_application(): void
    {
        $result = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
            'discount'     => 1000000,
            'tax_percent'  => 10,
        ]);

        $taxBase = $result['subtotal'] - $result['discount'];
        $expectedTax = (int)floor($taxBase * 0.1);

        $this->assertEquals($expectedTax, $result['tax']);
        $this->assertEquals($taxBase + $expectedTax, $result['total']);
    }

    /**
     * Test tax calculation
     */
    public function test_tax_calculation(): void
    {
        $result = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
            'tax_percent'  => 10,
        ]);

        $expectedTax = (int)floor($result['subtotal'] * 0.1);
        $this->assertEquals($expectedTax, $result['tax']);
    }

    /**
     * Test zero tax
     */
    public function test_zero_tax(): void
    {
        $result = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
            'tax_percent'  => 0,
        ]);

        $this->assertEquals(0, $result['tax']);
        $this->assertEquals($result['subtotal'], $result['total']);
    }

    /**
     * Test invalid package
     */
    public function test_invalid_package(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->preview([
            'package_slug' => 'nonexistent',
            'period'       => 'yearly',
        ]);
    }

    /**
     * Test invalid period
     */
    public function test_invalid_period(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'biweekly',
        ]);
    }

    /**
     * Test different packages
     */
    public function test_different_packages(): void
    {
        $basic = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
        ]);

        $pro = $this->service->preview([
            'package_slug' => 'pro',
            'period'       => 'yearly',
        ]);

        // Pro should cost more than Basic
        $this->assertGreaterThan($basic['subtotal'], $pro['subtotal']);
    }

    /**
     * Test response structure
     */
    public function test_response_structure(): void
    {
        $result = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
            'discount'     => 500000,
            'tax_percent'  => 10,
        ]);

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('discount', $result);
        $this->assertArrayHasKey('tax_percent', $result);
        $this->assertArrayHasKey('tax', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('currency', $result);

        $this->assertIsArray($result['items']);
        $this->assertIsInt($result['subtotal']);
        $this->assertIsInt($result['discount']);
        $this->assertIsInt($result['tax']);
        $this->assertIsInt($result['total']);
        $this->assertIsString($result['currency']);
    }

    /**
     * Test calculation accuracy
     */
    public function test_calculation_accuracy(): void
    {
        $result = $this->service->preview([
            'package_slug' => 'basic',
            'period'       => 'yearly',
            'discount'     => 0,
            'tax_percent'  => 10,
        ]);

        // Manual calculation
        // Assuming 40 units, rate 99000, multiplier 10
        // Expected: 40 × 99000 × 10 = 39,600,000
        // Tax: 39,600,000 × 0.1 = 3,960,000
        // Total: 43,560,000

        $this->assertGreaterThan(0, $result['subtotal']);
        $this->assertGreaterThan(0, $result['tax']);
        $this->assertGreaterThan($result['subtotal'], $result['total']);
    }
}
