<?php

use Lucid\Str;

// Test cases for studly()

it('converts string to studly', function () {
    $this->assertEquals('StudlyCase', Str::studly('studly_case'));
});

it('converts null to empty', function () {
    $this->assertEquals('', Str::studly(null));
});

// Test cases for snake()

it('converts string to snake', function () {
    $this->assertEquals('snake_case', Str::snake('snakeCase'));
});

it('converts string to snake with custom delimiter', function () {
    $this->assertEquals('snake-case', Str::snake('snakeCase', '-'));
});

// Test cases for replaceLast()

it('replaces last occurrence of string', function () {
    $this->assertEquals('foo bar qux', Str::replaceLast('baz', 'qux', 'foo bar baz'));
});

it('replaces last occurrence of string with empty string', function () {
    $this->assertEquals('foo bar ', Str::replaceLast('baz', '', 'foo bar baz'));
});

it('replaces last occurrence of string with empty string when string ends with search', function () {
    $this->assertEquals('foo ', Str::replaceLast('bar', '', 'foo bar'));
});

it('replaces last occurrence of string with empty string when string is search', function () {
    $this->assertEquals('', Str::replaceLast('foo', '', 'foo'));
});

// Test cases for substr()

it('returns empty string when offset is greater than string length', function () {
    $this->assertEquals('', Str::substr('foo', 3));
});

it('returns empty string when offset is equal to string length', function () {
    $this->assertEquals('', Str::substr('foo', 3));
});

it('returns correct substring', function () {
    $this->assertEquals('oo', Str::substr('foo', 1));
});

// Test cases for realName()

it('returns real name', function () {
    $this->assertEquals('Create Article', Str::realName('CreateArticleFeature.php', '/Feature.php/'));
});

// Test cases for feature()

it('returns feature name', function () {
    $this->assertEquals('CreateArticleFeature', Str::feature('CreateArticleFeature.php'));
});

it('returns feature name variation 2', function () {
    $this->assertEquals('CreateArticleFeature', Str::feature('Create Article Feature'));
});

it('returns feature name variation 3', function () {
    $this->assertEquals('CreateArticleFeature', Str::feature('Create Article'));
});

it('returns feature name variation 4', function () {
    $this->assertEquals('CreateArticleFeature', Str::feature('CreateArticle'));
});

// Test cases for job()

it('returns job name', function () {
    $this->assertEquals('CreateArticleJob', Str::job('CreateArticleJob.php'));
});

it('returns job name variation 2', function () {
    $this->assertEquals('CreateArticleJob', Str::job('Create Article Job'));
});

it('returns job name variation 3', function () {
    $this->assertEquals('CreateArticleJob', Str::job('Create Article'));
});

it('returns job name variation 4', function () {
    $this->assertEquals('CreateArticleJob', Str::job('CreateArticle'));
});

// Test cases for operation()

it('returns operation name', function () {
    $this->assertEquals('CreateArticleOperation', Str::operation('CreateArticleOperation.php'));
});

it('returns operation name variation 2', function () {
    $this->assertEquals('CreateArticleOperation', Str::operation('Create Article Operation'));
});

it('returns operation name variation 3', function () {
    $this->assertEquals('CreateArticleOperation', Str::operation('Create Article'));
});

it('returns operation name variation 4', function () {
    $this->assertEquals('CreateArticleOperation', Str::operation('CreateArticle'));
});

// Test cases for domain()

it('returns domain name', function () {
    $this->assertEquals('TestDomain', Str::domain('testDomain'));
});

it('returns domain name variation 2', function () {
    $this->assertEquals('TestDomain', Str::domain('test_domain'));
});

// Test cases for service()

it('returns service name', function () {
    $this->assertEquals('TestService', Str::service('testService'));
});

it('returns service name variation 2', function () {
    $this->assertEquals('TestService', Str::service('test_service'));
});

// Test cases for controller()

it('returns controller name', function () {
    $this->assertEquals('TestController', Str::controller('testController'));
});

it('returns controller name variation 2', function () {
    $this->assertEquals('TestController', Str::controller('testController.php'));
});

it('returns controller name variation 3', function () {
    $this->assertEquals('TestController', Str::controller('test'));
});

// Test cases for model()

it('returns model name', function () {
    $this->assertEquals('TestModel', Str::model('testModel'));
});

it('returns model name variation 2', function () {
    $this->assertEquals('TestModel', Str::model('test_model'));
});

// Test cases for policy()

it('returns policy name', function () {
    $this->assertEquals('TestPolicy', Str::policy('testPolicy'));
});

it('returns policy name variation 2', function () {
    $this->assertEquals('TestPolicy', Str::policy('testPolicy.php'));
});

it('returns policy name variation 3', function () {
    $this->assertEquals('TestPolicy', Str::policy('test'));
});

// Test cases for request()

it('returns request name', function () {
    $this->assertEquals('TestRequest', Str::request('testRequest'));
});

it('returns request name variation 2', function () {
    $this->assertEquals('TestRequest', Str::request('test_request'));
});
