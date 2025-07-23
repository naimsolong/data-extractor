<?php

use Illuminate\Database\Eloquent\Model;
use NaimSolong\DataExtractor\SourcesResolver;

class SourcesResolverTestModel extends Model
{
    protected $table = 'sources_resolver_test_models';
}

beforeEach(function () {
    config([
        'data-extractor.source' => [
            'default' => [
                'model' => SourcesResolverTestModel::class,
                'connection' => 'mysql',
                'relationships' => ['profile', 'posts'],
            ],
            'testing_source' => [
                'model' => SourcesResolverTestModel::class,
                'connection' => 'testing',
                'relationships' => ['comments'],
            ],
            'minimal_source' => [
                'model' => SourcesResolverTestModel::class,
                // connection and relationships will use defaults
            ],
        ],
    ]);
});

it('can get source by key', function () {
    $resolver = new SourcesResolver;
    $source = $resolver->set('default')->get();

    expect($source)->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Source::class);
    expect($source->model)->toBe(SourcesResolverTestModel::class);
    expect($source->connection)->toBe('mysql');
    expect($source->relationships)->toBe(['profile', 'posts']);
});

it('can get different source configurations', function () {
    $resolver = new SourcesResolver;
    $source = $resolver->set('testing_source')->get();

    expect($source->connection)->toBe('testing');
    expect($source->relationships)->toBe(['comments']);
});

it('can handle minimal source configuration with defaults', function () {
    $resolver = new SourcesResolver;
    $source = $resolver->set('minimal_source')->get();

    expect($source->model)->toBe(SourcesResolverTestModel::class);
    expect($source->connection)->toBe('testing'); // Should be testing
    expect($source->relationships)->toBe([]); // Should default to empty array
});

it('throws exception for non-existent source', function () {
    $resolver = new SourcesResolver;

    expect(fn () => $resolver->get('non_existent_source'))
        ->toThrow('Source has not been set. Please call set() method with a valid source key.');
});

it('throws exception for malformed source configuration', function () {
    config([
        'data-extractor.source' => [
            'malformed' => [
                // Missing required model field
                'connection' => 'mysql',
                'relationships' => [],
            ],
        ],
    ]);

    $resolver = new SourcesResolver;

    expect(fn () => $resolver->set('malformed'))
        ->toThrow('Undefined array key "model"');
});

it('validates model class in source configuration', function () {
    config([
        'data-extractor.source' => [
            'invalid_model' => [
                'model' => 'NonExistentModelClass',
                'connection' => 'mysql',
                'relationships' => [],
            ],
        ],
    ]);

    $resolver = new SourcesResolver;

    expect(fn () => $resolver->get('invalid_model'))
        ->toThrow('Source has not been set. Please call set() method with a valid source key.');
});

it('handles empty source configuration', function () {
    config(['data-extractor.source' => []]);

    $resolver = new SourcesResolver;

    expect(fn () => $resolver->set('any_key'))
        ->toThrow('Invalid source value: any_key');
});

it('can handle various connection types', function () {
    config([
        'data-extractor.source' => [
            'mysql_source' => [
                'model' => SourcesResolverTestModel::class,
                'connection' => 'mysql',
                'relationships' => [],
            ],
            'pgsql_source' => [
                'model' => SourcesResolverTestModel::class,
                'connection' => 'pgsql',
                'relationships' => [],
            ],
            'sqlite_source' => [
                'model' => SourcesResolverTestModel::class,
                'connection' => 'sqlite',
                'relationships' => [],
            ],
        ],
    ]);

    $resolver = new SourcesResolver;

    expect($resolver->set('mysql_source')->get()->connection)->toBe('mysql');
    expect($resolver->set('pgsql_source')->get()->connection)->toBe('pgsql');
    expect($resolver->set('sqlite_source')->get()->connection)->toBe('sqlite');
});

it('can handle complex relationship configurations', function () {
    config([
        'data-extractor.source' => [
            'complex_relationships' => [
                'model' => SourcesResolverTestModel::class,
                'connection' => 'mysql',
                'relationships' => [
                    'profile',
                    'posts.comments',
                    'orders.items.product',
                    'roles.permissions.modules',
                ],
            ],
        ],
    ]);

    $resolver = new SourcesResolver;
    $source = $resolver->set('complex_relationships')->get();

    expect($source->relationships)->toBe([
        'profile',
        'posts.comments',
        'orders.items.product',
        'roles.permissions.modules',
    ]);
});

it('returns consistent Source DTO instances', function () {
    $resolver = new SourcesResolver;
    $source1 = $resolver->set('default')->get();
    $source2 = $resolver->set('default')->get();

    // Should be equivalent but separate instances
    expect($source1)->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Source::class);
    expect($source2)->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Source::class);
    expect($source1->model)->toBe($source2->model);
    expect($source1->connection)->toBe($source2->connection);
    expect($source1->relationships)->toBe($source2->relationships);
});
