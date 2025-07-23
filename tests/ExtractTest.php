<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NaimSolong\DataExtractor\Extract;

beforeEach(function () {
    config(['data-extractor.is_enabled' => true]);

    Schema::create('test_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    });

    Schema::create('test_profiles', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('bio')->nullable();
        $table->timestamps();
    });

    Schema::create('test_posts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_posts');
    Schema::dropIfExists('test_profiles');
    Schema::dropIfExists('test_users');
});

class TestUser extends Model
{
    protected $table = 'test_users';

    protected $guarded = [];

    public function profile()
    {
        return $this->hasOne(TestProfile::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(TestPost::class, 'user_id');
    }
}

class TestProfile extends Model
{
    protected $table = 'test_profiles';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(TestUser::class, 'user_id');
    }
}

class TestPost extends Model
{
    protected $table = 'test_posts';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(TestUser::class, 'user_id');
    }
}

it('can extract data using source method', function () {
    config([
        'data-extractor.source.test' => [
            'connection' => 'testing',
            'model' => TestUser::class,
            'relationships' => ['profile', 'posts'],
        ],
    ]);

    $user = TestUser::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    TestProfile::create([
        'user_id' => $user->id,
        'bio' => 'Software Developer',
    ]);

    TestPost::create([
        'user_id' => $user->id,
        'title' => 'First Post',
        'content' => 'This is my first post',
    ]);

    $extract = new Extract;
    $results = $extract
        ->source('test')
        ->queryId($user->id)
        ->toSql();

    expect($results)->toBeArray();
    expect($results)->toHaveCount(3); // User + Profile + Post
    expect($results[0])->toContain('INSERT INTO test_users');
    expect($results[0])->toContain('John Doe');
    expect($results[1])->toContain('INSERT INTO test_profiles');
    expect($results[1])->toContain('Software Developer');
    expect($results[2])->toContain('INSERT INTO test_posts');
    expect($results[2])->toContain('First Post');
});

it('can extract data using option method', function () {
    config([
        'data-extractor.options.0' => [
            'name' => 'TestOption',
            'description' => 'Test extraction option',
            'format' => 'sql',
            'source' => 'test',
        ],
        'data-extractor.source.test' => [
            'connection' => 'testing',
            'model' => TestUser::class,
            'relationships' => [],
        ],
    ]);

    $user = TestUser::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $extract = new Extract;
    $results = $extract
        ->option('TestOption')
        ->queryId($user->id)
        ->toSql();

    expect($results)->toBeArray();
    expect($results)->toHaveCount(1);
    expect($results[0])->toContain('INSERT INTO test_users');
    expect($results[0])->toContain('Jane Doe');
});

it('can extract multiple models by ID array', function () {
    config([
        'data-extractor.source.test' => [
            'connection' => 'testing',
            'model' => TestUser::class,
            'relationships' => [],
        ],
    ]);

    $user1 = TestUser::create(['name' => 'User 1', 'email' => 'user1@example.com']);
    $user2 = TestUser::create(['name' => 'User 2', 'email' => 'user2@example.com']);

    $extract = new Extract;
    $results = $extract
        ->source('test')
        ->queryId([$user1->id, $user2->id])
        ->toSql();

    expect($results)->toBeArray();
    expect($results)->toHaveCount(2);
    expect(implode('', $results))->toContain('User 1');
    expect(implode('', $results))->toContain('User 2');
});

it('throws exception when data extractor is disabled', function () {
    config(['data-extractor.is_enabled' => false]);

    expect(fn () => new Extract)
        ->toThrow('Data Extractor is not enabled. Please check your configuration.');
});

it('throws exception for invalid model class', function () {
    config([
        'data-extractor.source.invalid' => [
            'connection' => 'testing',
            'model' => 'InvalidModel',
            'relationships' => [],
        ],
    ]);

    $extract = new Extract;

    expect(fn () => $extract->source('invalid')->queryId(1)->toSql())
        ->toThrow('The provided model, parent must be an instance of Illuminate\Database\Eloquent\Model');
});

it('handles missing relationships gracefully', function () {
    config([
        'data-extractor.source.test' => [
            'connection' => 'testing',
            'model' => TestUser::class,
            'relationships' => ['nonExistentRelation'],
        ],
    ]);

    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $extract = new Extract;
    expect(fn () => $extract->source('test')->queryId($user->id)->toSql())
        ->toThrow('Call to undefined relationship [nonExistentRelation] on model [TestUser]');
});

it('prevents circular relationship extraction', function () {
    config([
        'data-extractor.source.test' => [
            'connection' => 'testing',
            'model' => TestUser::class,
            'relationships' => ['profile'],
        ],
    ]);

    $user = TestUser::create(['name' => 'Test User', 'email' => 'test@example.com']);
    $profile = TestProfile::create(['user_id' => $user->id, 'bio' => 'Test Bio']);

    $extract = new Extract;
    $results = $extract
        ->source('test')
        ->queryId($user->id)
        ->toSql();

    expect($results)->toBeArray();
    expect($results)->toHaveCount(2); // User + Profile (no infinite loop)
});

it('can extract to CSV format', function () {
    config([
        'data-extractor.source.test' => [
            'connection' => 'testing',
            'model' => TestUser::class,
            'relationships' => [],
        ],
    ]);

    $user = TestUser::create([
        'name' => 'CSV User',
        'email' => 'csv@example.com',
    ]);

    $extract = new Extract;
    $results = $extract
        ->source('test')
        ->queryId($user->id)
        ->toCsv();

    expect($results)->toBeArray();
    expect($results[0])->toContain('id,name,email');
    expect($results[0])->toContain('"CSV User","csv@example.com"');
});
