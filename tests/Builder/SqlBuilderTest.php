<?php

use NaimSolong\DataExtractor\Builder\SqlBuilder;

it('can build SQL INSERT statement with basic data', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'email', 'created_at']);
    $builder->setData([
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => '2023-01-01 12:00:00',
    ]);

    $result = $builder->build();

    expect($result)->toBeString();
    expect($result)->toBe("INSERT INTO test_table (id, name, email, created_at) VALUES (1, 'John Doe', 'john@example.com', '2023-01-01 12:00:00');");
});

it('can handle NULL values', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'bio']);
    $builder->setData([
        'id' => 1,
        'name' => 'John Doe',
        'bio' => null,
    ]);

    $result = $builder->build();

    expect($result)->toBe("INSERT INTO test_table (id, name, bio) VALUES (1, 'John Doe', NULL);");
});

it('can handle boolean values', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'is_active', 'is_admin']);
    $builder->setData([
        'id' => 1,
        'is_active' => true,
        'is_admin' => false,
    ]);

    $result = $builder->build();

    expect($result)->toBe("INSERT INTO test_table (id, is_active, is_admin) VALUES (1, '1', '0');");
});

it('can handle array values as JSON', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'metadata']);
    $builder->setData([
        'id' => 1,
        'metadata' => ['key' => 'value', 'count' => 5],
    ]);

    $result = $builder->build();

    expect($result)->toContain('{"key":"value","count":5}');
});

it('can handle missing columns with placeholder', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'email']);
    $builder->setData([
        'id' => 1,
        'name' => 'John Doe',
        // email is missing
    ]);

    $result = $builder->build();

    expect($result)->toContain("'*****'"); // Based on actual implementation
});

it('can handle DateTime objects', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'created_at']);
    $builder->setData([
        'id' => 1,
        'created_at' => new DateTime('2023-01-01 12:00:00'),
    ]);

    $result = $builder->build();

    expect($result)->toContain('2023-01-01'); // DateTime formats as date only in implementation
});

it('can handle Carbon objects', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'updated_at']);
    $builder->setData([
        'id' => 1,
        'updated_at' => \Carbon\Carbon::parse('2023-06-15 14:30:00'),
    ]);

    $result = $builder->build();

    expect($result)->toContain('2023-06-15'); // Carbon extends DateTime
});

it('can handle numeric values', function () {
    $builder = new SqlBuilder;
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'price', 'count']);
    $builder->setData([
        'id' => 1,
        'price' => 19.99,
        'count' => 42,
    ]);

    $result = $builder->build();

    expect($result)->toContain('VALUES (1, 19.99, 42);');
});
