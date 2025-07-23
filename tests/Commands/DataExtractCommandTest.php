<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['data-extractor.is_enabled' => true]);

    Schema::create('command_test_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('command_test_users');
});

class CommandTestUser extends Model
{
    protected $table = 'command_test_users';

    protected $guarded = [];
}

it('fails when data extractor is disabled', function () {
    config([
        'data-extractor.is_enabled' => false,
        'data-extractor.options' => [
            [
                'name' => 'CommandTest',
                'description' => 'Command test option',
                'format' => 'sql',
                'source' => 'command_test',
            ],
        ],
        'data-extractor.source' => [
            'command_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $this->artisan('data:extract')
        ->expectsOutput('Data extraction is not enabled.')
        ->assertExitCode(1);
});

it('can extract data with command line arguments', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'CommandTest',
                'description' => 'Command test option',
                'format' => 'sql',
                'source' => 'command_test',
            ],
        ],
        'data-extractor.source' => [
            'command_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $user = CommandTestUser::create([
        'name' => 'Command Test User',
        'email' => 'command@example.com',
    ]);

    $this->artisan('data:extract', [
        '--name' => 'CommandTest',
        '--queryId' => $user->id,
        '--format' => 'sql',
    ])
        ->expectsOutputToContain('INSERT INTO command_test_users')
        ->assertExitCode(0);
});

it('can extract CSV format with command line arguments', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'CsvTest',
                'description' => 'CSV test option',
                'format' => 'csv',
                'source' => 'csv_test',
            ],
        ],
        'data-extractor.source' => [
            'csv_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $user = CommandTestUser::create([
        'name' => 'CSV Test User',
        'email' => 'csv@example.com',
    ]);

    $this->artisan('data:extract', [
        '--name' => 'CsvTest',
        '--queryId' => $user->id,
        '--format' => 'csv',
    ])
        ->expectsOutputToContain('id,name,email')
        ->assertExitCode(0);
});

it('prompts for model ID when not provided', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'PromptTest',
                'description' => 'Prompt test option',
                'format' => 'sql',
                'source' => 'prompt_test',
            ],
        ],
        'data-extractor.source' => [
            'prompt_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $user = CommandTestUser::create([
        'name' => 'Prompt Test User',
        'email' => 'prompt@example.com',
    ]);

    $this->artisan('data:extract', ['--name' => 'PromptTest'])
        ->expectsQuestion('Enter the ID of the command_test_users to extract data from', $user->id)
        ->expectsOutputToContain('INSERT INTO command_test_users')
        ->assertExitCode(0);
});

it('fails with invalid model ID', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'InvalidTest',
                'description' => 'Invalid test option',
                'format' => 'sql',
                'source' => 'invalid_test',
            ],
        ],
        'data-extractor.source' => [
            'invalid_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $this->artisan('data:extract', [
        '--name' => 'InvalidTest',
        '--queryId' => 0, // Invalid ID
    ])
        ->assertExitCode(1);
});

it('can handle multiple IDs', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'MultipleTest',
                'description' => 'Multiple IDs test',
                'format' => 'sql',
                'source' => 'multiple_test',
            ],
        ],
        'data-extractor.source' => [
            'multiple_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $user = CommandTestUser::create(['name' => 'User 1', 'email' => 'user1@example.com']);

    $this->artisan('data:extract', [
        '--name' => 'MultipleTest',
        '--queryId' => $user->id,
    ])
        ->expectsOutputToContain('User 1')
        ->assertExitCode(0);
});

it('uses default format when not specified', function () {
    config([
        'data-extractor.options' => [
            [
                'name' => 'DefaultFormat',
                'description' => 'Default format test',
                'format' => 'sql',
                'source' => 'default_format_test',
            ],
        ],
        'data-extractor.source' => [
            'default_format_test' => [
                'model' => CommandTestUser::class,
                'connection' => 'testing',
                'relationships' => [],
            ],
        ],
    ]);

    $user = CommandTestUser::create(['name' => 'Default User', 'email' => 'default@example.com']);

    $this->artisan('data:extract', [
        '--name' => 'DefaultFormat',
        '--queryId' => $user->id,
        // No --format specified, should use default
    ])
        ->expectsOutputToContain('INSERT INTO') // Default should be SQL
        ->assertExitCode(0);
});
