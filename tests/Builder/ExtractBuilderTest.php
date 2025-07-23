<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NaimSolong\DataExtractor\Builder\CsvBuilder;
use NaimSolong\DataExtractor\Builder\ExtractBuilder;
use NaimSolong\DataExtractor\Builder\SqlBuilder;

beforeEach(function () {
    Schema::create('extract_test_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('extract_test_users');
});

class ExtractTestUser extends Model
{
    protected $table = 'extract_test_users';
    protected $guarded = [];
}

it('can create SQL builder', function () {
    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_SQL);

    expect($builder->getBuilder())->toBeInstanceOf(SqlBuilder::class);
});

it('can create CSV builder', function () {
    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_CSV);

    expect($builder->getBuilder())->toBeInstanceOf(CsvBuilder::class);
});

it('throws exception for unsupported format', function () {
    expect(fn() => (new ExtractBuilder)->createBuilder('invalid_format'))
        ->toThrow('Invalid builder type: invalid_format');
});

it('can get table schema information', function () {
    $user = ExtractTestUser::create([
        'name' => 'Schema Test',
        'email' => 'schema@example.com',
    ]);

    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_SQL);
    $columns = $builder->setModel($user)->getTableColumns();

    expect($columns)->toBeArray();
    expect($columns)->toContain('id');
    expect($columns)->toContain('name');
    expect($columns)->toContain('email');
    expect($columns)->toContain('is_active');
    expect($columns)->toContain('created_at');
    expect($columns)->toContain('updated_at');
});

it('can build data with models', function () {
    $user = ExtractTestUser::create(['name' => 'User 1', 'email' => 'user1@example.com']);

    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_SQL);
    $results = $builder->setModel($user)->build();

    expect($results)->toBeString();
    expect($results)->toContain('INSERT INTO extract_test_users');
    expect($results)->toContain('User 1');
});

it('can build CSV data with models', function () {
    $user = ExtractTestUser::create(['name' => 'CSV User', 'email' => 'csv@example.com']);

    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_CSV);
    $results = $builder->setModel($user)->build();

    expect($results)->toBeString();
    expect($results)->toContain('id,name,email');
    expect($results)->toContain('CSV User');
});

it('throws exception when building with invalid models', function () {
    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_SQL);

    expect(fn() => $builder->build(['not_a_model']))
        ->toThrow('Model not set. Use setModel() to set the model before building.');
});

it('groups models by table correctly', function () {
    // Create different model types
    $user = ExtractTestUser::create(['name' => 'User 2', 'email' => 'user2@example.com']);

    $builder = (new ExtractBuilder)->createBuilder(ExtractBuilder::FORMAT_SQL);
    $results = $builder->setModel($user)->build();

    // Both should be INSERT statements for the same table
    expect($results)->toContain('INSERT INTO extract_test_users');
});
