# Arcanist Extensions

This project provides some useful extensions for [Arcanist](https://github.com/phacility/arcanist).

## Extensions

- [Multi Test Engine](#multi_test_engine)
- [RSpec Test Engine](#rspec_test_engine)
- [Rubocop Linter](#rubocop_linter)
- [TAP Test Engine](#tap_test_engine)

## Installation

The easiest way to include some (or all) the extensions on your own projects is by adding this repository as git submodule, given you are using git (which you obviously should):

```
$ git submodule add https://github.com/tagview/arcanist-extensions.git .arcanist-extensions
$ git submodule update --init
```

Then, just list the desired extensions on the `load` key of your project's `.arcconfig` file.

```json
{
  "project_id": "my-awesome-project",
  "conduit_uri": "https://example.org",

  "load": [
    ".arcanist-extensions/[extension_name]"
  ]
}
```

## Available extensions

### `multi_test_engine`

This extensions allows you to run tests with multiple engines. It is usefull when your project has code writenn in more than one programming languages.

Above is an example of a `.arcconfig` file that runs both Ruby tests - with the [`RSpecTestEngine`](#rspec_test_engine) - and Python tests - with the native Arcanist `PytestTestEngine`:

```json
{
  "project_id": "my-awesome-project",
  "conduit_uri": "https://example.org",

  "load": [
    ".arcanist-extensions/rspec_test_engine",
    ".arcanist-extensions/multi_test_engine"
  ],

  "unit.engine": "MultiTestEngine",
  "unit.engine.multi-test.engines": ["RSpecTestEngine", "PytestTestEngine"]
}
```

You may also want to specify a configuration for each engine. For example, here we are using two [`TapTestEngines`](#tap_test_engine) with different commands:

```json
{
  "project_id": "my-awesome-project",
  "conduit_uri": "https://example.org",

  "load": [
    ".arcanist-extensions/rspec_test_engine",
    ".arcanist-extensions/tap_test_engine",
    ".arcanist-extensions/multi_test_engine"
  ],

  "unit.engine": "MultiTestEngine",
  "unit.engine.multi-test": [
    "RSpecTestEngine",
    {
      "engine": "TapTestEngine",
      "unit.engine.tap.command": "bundle exec teaspoon -f tap"
    },
    {
      "engine": "TapTestEngine",
      "unit.engine.tap.command": "karma run spec/js/karma.conf"
    }
  ]
}
```

### `rspec_test_engine`

This extension allows you to run tests with [RSpec](http://rspec.info/) (version 3 or later). To use it, just set your test engine as `RSpecTestEngine`:

```json
{
  "project_id": "my-awesome-project",
  "conduit_uri": "https://example.org",

  "load": [
    ".arcanist-extensions/rspec_test_engine"
  ],

  "unit.engine": "RSpecTestEngine"
}
```

### `rubocop_linter`

This extension will lint your project using the awesome [Rubocop](https://github.com/bbatsov/rubocop) library. It is important to mention that the extension won't install Rubocop, so you must do it manually and make sure you have the `rubocop` executable listed on your `$PATH`.

Below is an example of an `.arclint` file that includes the Rubocop Linter:

```json
{
  "linters": {
    "ruby": {
      "type": "rubocop",
      "include": "/\\.(rb|rake)$/",
      "exclude": "(^db/schema\\.rb)"
    }
  }
}
```

For more information regarding Arcanist linters configuration, access the [Arcanist Lint User Guide](https://secure.phabricator.com/book/phabricator/article/arcanist_lint/).

If you need to customize the default style rules, just create a `.rubocop.yml` file on the root of your project as you would normally do.

### `tap_test_engine`

This extension implements a generic [TAP](http://testanything.org/) test engine, so Arcanist may run tests from any tool that implements this protocol.

To use this extension, you must specify the command that will run your tests (just remember that this command must return a TAP compatible output):

```json
{
  "project_id": "my-awesome-project",
  "conduit_uri": "https://example.org",

  "load": [
    ".arcanist-extensions/tap_test_engine"
  ],

  "unit.engine": "TAPTestEngine",
  "unit.engine.tap.command": "bundle exec rake spec"
}
```
