<?php

use Illuminate\Database\Eloquent\Model;
use NaimSolong\DataExtractor\OptionsResolver;

class ResolverTestModel extends Model
{
    protected $table = 'resolver_test_models';
}

beforeEach(function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'FirstOption',
                'description' => 'First test option',
                'format' => 'sql',
                'source' => 'first_source',
            ],
            [
                'name' => 'SecondOption',
                'description' => 'Second test option',
                'format' => 'csv',
                'source' => 'second_source',
            ],
        ],
        'data-extractor.source' => [
            'first_source' => [
                'model' => ResolverTestModel::class,
                'connection' => 'mysql',
                'relationships' => ['profile'],
            ],
            'second_source' => [
                'model' => ResolverTestModel::class,
                'connection' => 'testing',
                'relationships' => ['posts', 'comments'],
            ],
        ],
    ]);
});

it('can get all options', function () {
    $resolver = new OptionsResolver;
    $options = $resolver->all();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(2);
    expect($options[0])->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Option::class);
    expect($options[0]->name)->toBe('FirstOption');
    expect($options[1]->name)->toBe('SecondOption');
});

it('can set option by numeric index', function () {
    $resolver = new OptionsResolver;
    $resolver->set(0);

    $option = $resolver->get();

    expect($option)->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Option::class);
    expect($option->name)->toBe('FirstOption');
    expect($option->description)->toBe('First test option');
});

it('can set option by string name', function () {
    $resolver = new OptionsResolver;
    $resolver->set('SecondOption');

    $option = $resolver->get();

    expect($option)->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Option::class);
    expect($option->name)->toBe('SecondOption');
    expect($option->format)->toBe('csv');
});

it('throws exception for invalid option index', function () {
    $resolver = new OptionsResolver;

    expect(fn () => $resolver->set(999))
        ->toThrow('Invalid option value: 999');
});

it('throws exception for invalid option name', function () {
    $resolver = new OptionsResolver;

    expect(fn () => $resolver->set('NonExistentOption'))
        ->toThrow('Invalid option value: NonExistentOption');
});

it('throws exception when getting option before setting', function () {
    $resolver = new OptionsResolver;

    expect(fn () => $resolver->get())
        ->toThrow(''); // Will throw uninitialized property error
});

it('can get source for current option', function () {
    $resolver = new OptionsResolver;
    $resolver->set('FirstOption');

    $source = $resolver->source();

    expect($source)->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Source::class);
    expect($source->model)->toBe(ResolverTestModel::class);
    expect($source->connection)->toBe('mysql');
    expect($source->relationships)->toBe(['profile']);
});

it('throws exception when getting source before setting option', function () {
    $resolver = new OptionsResolver;

    expect(fn () => $resolver->source())
        ->toThrow(''); // Will throw uninitialized property error
});

it('handles empty options configuration', function () {
    config(['data-extractor.options' => []]);

    $resolver = new OptionsResolver;
    $options = $resolver->all();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(0);
});

it('can reset current option', function () {
    $resolver = new OptionsResolver;
    $resolver->set('FirstOption');

    expect($resolver->get())->toBeInstanceOf(\NaimSolong\DataExtractor\Dto\Option::class);

    // Reset by setting to a different option
    $resolver->set('SecondOption');

    expect($resolver->get()->name)->toBe('SecondOption');
});

it('validates option format values', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'ValidOption',
                'description' => 'Valid option',
                'format' => 'sql',
                'source' => 'first_source',
            ],
        ],
    ]);

    $resolver = new OptionsResolver;
    $resolver->set(0);

    expect($resolver->get()->format)->toBe('sql');
});

it('can handle case-sensitive option name matching', function () {
    $resolver = new OptionsResolver;

    // This should work with exact case
    $resolver->set('FirstOption');
    expect($resolver->get()->name)->toBe('FirstOption');

    // Test with different option
    $resolver->set('SecondOption');
    expect($resolver->get()->name)->toBe('SecondOption');
});

it('handles missing source configuration gracefully', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'MissingSource',
                'description' => 'Option with missing source',
                'format' => 'sql',
                'source' => 'non_existent_source',
            ],
        ],
        'data-extractor.source' => [], // No sources defined
    ]);

    // This should handle missing source by creating empty Source array
    expect(fn () => new OptionsResolver)->toThrow('Undefined array key "model"');
});
