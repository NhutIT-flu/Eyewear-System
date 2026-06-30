<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;

/**
 * Concrete test model to test Core\Model attribute logic.
 * Extends Model but overrides db-dependent methods.
 */
class TestableModel extends \Core\Model
{
    protected static string $table = 'test_table';
}

class ConventionModel extends \Core\Model
{
    // No $table override — should use class name convention
}

/**
 * Unit tests for Core\Model — attribute access and table name resolution.
 * Tests only the non-DB logic (magic getters/setters, toArray, getTable).
 */
class CoreModelTest extends TestCase
{
    // ─────────────────────────────────────────────────────────
    // Constructor
    // ─────────────────────────────────────────────────────────

    public function test_constructor_sets_attributes(): void
    {
        $model = new TestableModel(['name' => 'John', 'email' => 'john@example.com']);
        $this->assertEquals('John', $model->name);
        $this->assertEquals('john@example.com', $model->email);
    }

    public function test_empty_constructor(): void
    {
        $model = new TestableModel();
        $this->assertNull($model->name);
    }

    // ─────────────────────────────────────────────────────────
    // Magic __get / __set / __isset
    // ─────────────────────────────────────────────────────────

    public function test_magic_get_returns_attribute(): void
    {
        $model = new TestableModel(['id' => 1, 'name' => 'Test']);
        $this->assertEquals(1, $model->id);
        $this->assertEquals('Test', $model->name);
    }

    public function test_magic_get_returns_null_for_missing(): void
    {
        $model = new TestableModel(['id' => 1]);
        $this->assertNull($model->nonexistent);
    }

    public function test_magic_set_updates_attribute(): void
    {
        $model = new TestableModel(['name' => 'Old']);
        $model->name = 'New';
        $this->assertEquals('New', $model->name);
    }

    public function test_magic_set_adds_new_attribute(): void
    {
        $model = new TestableModel();
        $model->email = 'test@test.com';
        $this->assertEquals('test@test.com', $model->email);
    }

    public function test_magic_isset_true_for_existing(): void
    {
        $model = new TestableModel(['name' => 'John']);
        $this->assertTrue(isset($model->name));
    }

    public function test_magic_isset_false_for_missing(): void
    {
        $model = new TestableModel();
        $this->assertFalse(isset($model->name));
    }

    // ─────────────────────────────────────────────────────────
    // toArray
    // ─────────────────────────────────────────────────────────

    public function test_to_array_returns_all_attributes(): void
    {
        $data = ['id' => 1, 'name' => 'Test', 'email' => 'test@test.com'];
        $model = new TestableModel($data);
        $this->assertEquals($data, $model->toArray());
    }

    public function test_to_array_empty_model(): void
    {
        $model = new TestableModel();
        $this->assertEquals([], $model->toArray());
    }

    public function test_to_array_reflects_modifications(): void
    {
        $model = new TestableModel(['name' => 'Old']);
        $model->name = 'New';
        $model->extra = 'added';

        $expected = ['name' => 'New', 'extra' => 'added'];
        $this->assertEquals($expected, $model->toArray());
    }

    // ─────────────────────────────────────────────────────────
    // getTable
    // ─────────────────────────────────────────────────────────

    public function test_get_table_returns_explicit_table(): void
    {
        $this->assertEquals('test_table', TestableModel::getTable());
    }

    public function test_get_table_convention_uses_class_name(): void
    {
        // ConventionModel has no explicit $table — uses class name lowercase
        $table = ConventionModel::getTable();
        $this->assertEquals('conventionmodel', $table);
    }

    // ─────────────────────────────────────────────────────────
    // Complex attribute types
    // ─────────────────────────────────────────────────────────

    public function test_attribute_with_array_value(): void
    {
        $model = new TestableModel(['tags' => ['php', 'laravel']]);
        $this->assertEquals(['php', 'laravel'], $model->tags);
    }

    public function test_attribute_with_numeric_values(): void
    {
        $model = new TestableModel(['price' => 99.99, 'quantity' => 10]);
        $this->assertEquals(99.99, $model->price);
        $this->assertEquals(10, $model->quantity);
    }

    public function test_attribute_with_boolean(): void
    {
        $model = new TestableModel(['active' => true]);
        $this->assertTrue($model->active);
    }

    public function test_attribute_with_null_value(): void
    {
        $model = new TestableModel(['deleted_at' => null]);
        $this->assertNull($model->deleted_at);
        // isset returns false for null values
        $this->assertFalse(isset($model->deleted_at));
    }
}
