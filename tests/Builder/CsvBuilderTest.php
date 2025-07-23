<?php

use NaimSolong\DataExtractor\Builder\CsvBuilder;

it('can build CSV output with headers', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'email']);
    $builder->setData([
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    
    $result = $builder->build();
    
    expect($result)->toBeString();
    expect($result)->toBe("id,name,email\n1,\"John Doe\",\"john@example.com\"\n");
});

it('can handle single row data', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('users');
    $builder->setColumns(['id', 'name']);
    $builder->setData([
        'id' => 1,
        'name' => 'User 1',
    ]);
    
    $result = $builder->build();
    
    expect($result)->toBeString();
    expect($result)->toBe("id,name\n1,\"User 1\"\n");
});

it('can handle values with commas', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'description']);
    $builder->setData([
        'id' => 1,
        'description' => 'This is a description, with commas',
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain('"This is a description, with commas"');
});

it('can handle values with quotes', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'quote_test']);
    $builder->setData([
        'id' => 1,
        'quote_test' => 'He said "Hello World"',
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain('"He said ""Hello World"""');
});

it('can handle NULL values', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'bio']);
    $builder->setData([
        'id' => 1,
        'name' => 'John Doe',
        'bio' => null,
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain('"John Doe",""');
});

it('can handle boolean values', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'is_active', 'is_admin']);
    $builder->setData([
        'id' => 1,
        'is_active' => true,
        'is_admin' => false,
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain('1,\'1\',\'0\'');
});

it('can handle array values as JSON strings', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'metadata']);
    $builder->setData([
        'id' => 1,
        'metadata' => ['key' => 'value', 'count' => 5],
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain('{"key":"value","count":5}');
});

it('can handle missing columns', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'email']);
    $builder->setData([
        'id' => 1,
        'name' => 'John Doe',
        // email is missing
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain("'*****'");
});

it('can handle empty data array', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'name', 'email']);
    $builder->setData([]);
    
    $result = $builder->build();
    
    expect($result)->toBe("id,name,email\n'*****','*****','*****'\n");
});

it('can handle DateTime objects', function () {
    $builder = new CsvBuilder();
    $builder->setSchemaName('test_table');
    $builder->setColumns(['id', 'created_at']);
    $builder->setData([
        'id' => 1,
        'created_at' => new DateTime('2023-01-01 12:00:00'),
    ]);
    
    $result = $builder->build();
    
    expect($result)->toContain('2023-01-01 12:00:00');
});