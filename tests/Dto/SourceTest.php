<?php

use Illuminate\Database\Eloquent\Model;
use NaimSolong\DataExtractor\Dto\Source;

class TestModelForSource extends Model
{
    protected $table = 'test_models';
}

it('can create Source from array', function () {
    $data = [
        'model' => TestModelForSource::class,
        'connection' => 'mysql',
        'relationships' => ['profile', 'posts'],
    ];

    $source = Source::fromArray($data);

    expect($source)->toBeInstanceOf(Source::class);
    expect($source->model)->toBe(TestModelForSource::class);
    expect($source->connection)->toBe('mysql');
    expect($source->relationships)->toBe(['profile', 'posts']);
});

it('can convert Source to array', function () {
    $source = new Source(
        model: TestModelForSource::class,
        connection: 'mysql',
        relationships: ['profile', 'posts']
    );

    $array = $source->toArray();

    expect($array)->toBeArray();
    expect($array['model'])->toBe(TestModelForSource::class);
    expect($array['connection'])->toBe('mysql');
    expect($array['relationships'])->toBe(['profile', 'posts']);
});

it('defaults to database.default connection when not specified', function () {
    config(['database.default' => 'testing']); // Set a known default

    $data = [
        'model' => TestModelForSource::class,
        'relationships' => [],
    ];

    $source = Source::fromArray($data);

    expect($source->connection)->toBe('testing'); // Should use config default
});

it('defaults to empty relationships when not specified', function () {
    $data = [
        'model' => TestModelForSource::class,
        'connection' => 'mysql',
    ];

    $source = Source::fromArray($data);

    expect($source->relationships)->toBe([]);
});

it('is readonly and immutable', function () {
    $source = new Source(
        model: TestModelForSource::class,
        connection: 'testing',
        relationships: ['test']
    );

    expect($source->model)->toBe(TestModelForSource::class);
    expect($source->connection)->toBe('testing');
    expect($source->relationships)->toBe(['test']);
});

it('can handle various connection names', function () {
    $connectionNames = ['mysql', 'pgsql', 'sqlite', 'testing', 'custom_connection'];

    foreach ($connectionNames as $connection) {
        $data = [
            'model' => TestModelForSource::class,
            'connection' => $connection,
            'relationships' => [],
        ];

        $source = Source::fromArray($data);
        expect($source->connection)->toBe($connection);
    }
});

it('can handle complex relationship arrays', function () {
    $relationships = [
        'profile',
        'posts.comments',
        'roles.permissions',
        'orders.items.product',
    ];

    $data = [
        'model' => TestModelForSource::class,
        'connection' => 'mysql',
        'relationships' => $relationships,
    ];

    $source = Source::fromArray($data);

    expect($source->relationships)->toBe($relationships);
});

it('can handle array relationships parameter', function () {
    $data = [
        'model' => TestModelForSource::class,
        'connection' => 'mysql',
        'relationships' => ['profile', 'posts'],
    ];

    $source = Source::fromArray($data);

    expect($source->relationships)->toBeArray();
    expect($source->relationships)->toBe(['profile', 'posts']);
});
