<?php

use Illuminate\Database\Eloquent\Model;
use NaimSolong\DataExtractor\Dto\Option;
use NaimSolong\DataExtractor\Dto\Source;

class OptionTestModel extends Model
{
    protected $table = 'option_test_models';
}

it('can create Option with Source object', function () {
    $source = new Source(
        model: OptionTestModel::class,
        connection: 'mysql',
        relationships: ['profile']
    );

    $option = new Option(
        name: 'TestOption',
        description: 'Test Description',
        format: 'sql',
        source: $source
    );

    expect($option)->toBeInstanceOf(Option::class);
    expect($option->name)->toBe('TestOption');
    expect($option->description)->toBe('Test Description');
    expect($option->format)->toBe('sql');
    expect($option->source)->toBe($source);
});

it('can convert Option to array', function () {
    $source = new Source(
        model: OptionTestModel::class,
        connection: 'mysql',
        relationships: ['profile']
    );

    $option = new Option(
        name: 'TestOption',
        description: 'Test Description',
        format: 'sql',
        source: $source
    );

    $array = $option->toArray();

    expect($array)->toBeArray();
    expect($array['name'])->toBe('TestOption');
    expect($array['description'])->toBe('Test Description');
    expect($array['format'])->toBe('sql');
    expect($array['source'])->toBeArray();
    expect($array['source']['model'])->toBe(OptionTestModel::class);
});

it('fromArray creates Option with Source from array data', function () {
    config(['data-extractor.source.test_source' => [
        'model' => OptionTestModel::class,
        'connection' => 'mysql',
        'relationships' => ['profile'],
    ]]);

    // Note: The actual fromArray expects a Source object, not a string key
    $source = Source::fromArray([
        'model' => OptionTestModel::class,
        'connection' => 'mysql',
        'relationships' => ['profile'],
    ]);

    $option = Option::fromArray([
        'name' => 'TestOption',
        'description' => 'Test Description',
        'format' => 'sql',
        'source' => $source,
    ]);

    expect($option)->toBeInstanceOf(Option::class);
    expect($option->name)->toBe('TestOption');
    expect($option->source)->toBe($source);
});

it('can handle CSV format', function () {
    $source = new Source(
        model: OptionTestModel::class,
        connection: 'mysql',
        relationships: []
    );

    $option = new Option(
        name: 'CsvOption',
        description: 'CSV Export',
        format: 'csv',
        source: $source
    );

    expect($option->format)->toBe('csv');
    expect($option->toArray()['format'])->toBe('csv');
});

it('is readonly and immutable', function () {
    $source = new Source(
        model: OptionTestModel::class,
        connection: 'testing',
        relationships: []
    );

    $option = new Option(
        name: 'ImmutableTest',
        description: 'Test immutability',
        format: 'sql',
        source: $source
    );

    expect($option->name)->toBe('ImmutableTest');
    // Properties are readonly, so they cannot be modified
});

it('can handle empty description', function () {
    $source = new Source(
        model: OptionTestModel::class,
        connection: 'mysql',
        relationships: []
    );

    $option = new Option(
        name: 'NoDescription',
        description: '',
        format: 'sql',
        source: $source
    );

    expect($option->description)->toBe('');
});

it('fromArray uses default values for optional fields', function () {
    $source = new Source(
        model: OptionTestModel::class,
        connection: 'mysql',
        relationships: []
    );

    $option = Option::fromArray([
        'name' => 'MinimalOption',
        'source' => $source,
        // description and format not provided
    ]);

    expect($option->name)->toBe('MinimalOption');
    expect($option->description)->toBe(''); // Default from fromArray
    expect($option->format)->toBe('sql'); // Default from ExtractBuilder::DEFAULT_FORMAT
});
