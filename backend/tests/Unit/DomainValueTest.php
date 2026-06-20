<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Orders\ProductionStep;
use App\Domain\Orders\ShipmentStatus;

/**
 * Unit tests for Domain value classes — ProductionStep & ShipmentStatus.
 * These are simple enum-like classes but important for business logic correctness.
 */
class DomainValueTest extends TestCase
{
    // ─────────────────────────────────────────────────────────
    // ProductionStep
    // ─────────────────────────────────────────────────────────

    public function test_production_step_constants_defined(): void
    {
        $this->assertEquals('lens_cutting', ProductionStep::LENS_CUTTING);
        $this->assertEquals('frame_mounting', ProductionStep::FRAME_MOUNTING);
        $this->assertEquals('qc_inspection', ProductionStep::QC_INSPECTION);
        $this->assertEquals('packaging', ProductionStep::PACKAGING);
        $this->assertEquals('ready_to_ship', ProductionStep::READY_TO_SHIP);
    }

    public function test_production_step_values_returns_all(): void
    {
        $values = ProductionStep::values();

        $this->assertIsArray($values);
        $this->assertCount(5, $values);
        $this->assertContains('lens_cutting', $values);
        $this->assertContains('frame_mounting', $values);
        $this->assertContains('qc_inspection', $values);
        $this->assertContains('packaging', $values);
        $this->assertContains('ready_to_ship', $values);
    }

    public function test_production_step_values_order(): void
    {
        $values = ProductionStep::values();
        $this->assertEquals('lens_cutting', $values[0]);
        $this->assertEquals('ready_to_ship', $values[4]);
    }

    // ─────────────────────────────────────────────────────────
    // ShipmentStatus
    // ─────────────────────────────────────────────────────────

    public function test_shipment_status_constants_defined(): void
    {
        $this->assertEquals('pending', ShipmentStatus::PENDING);
        $this->assertEquals('in_transit', ShipmentStatus::IN_TRANSIT);
        $this->assertEquals('delivered', ShipmentStatus::DELIVERED);
        $this->assertEquals('returned', ShipmentStatus::RETURNED);
    }

    public function test_shipment_status_values_returns_all(): void
    {
        $values = ShipmentStatus::values();

        $this->assertIsArray($values);
        $this->assertCount(4, $values);
        $this->assertContains('pending', $values);
        $this->assertContains('in_transit', $values);
        $this->assertContains('delivered', $values);
        $this->assertContains('returned', $values);
    }

    public function test_shipment_status_values_order(): void
    {
        $values = ShipmentStatus::values();
        $this->assertEquals('pending', $values[0]);
        $this->assertEquals('returned', $values[3]);
    }
}
