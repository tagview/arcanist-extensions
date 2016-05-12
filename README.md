# Arcanist Extensions

This project provides some useful extensions for [Arcanist](https://github.com/phacility/arcanist).

## Extensions

- [Multi Test Engine](#multi_test_engine)
- [RSpec Test Engine](#rspec_test_engine)
- [Rubocop Linter](#rubocop_linter)
- [TAP Test Engine](#tap_test_engine)
- [ESlint Linter](#eslint_linter)

## Installation

The easiest way to use any of these extensions on your own project, is by adding this repository as a git submodule, given you are using git (which you obviously should):

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

This extension allows you to run tests with multiple test engines. It is usefull when your project has code writen in more than one programming language, or when your project uses two different testing frameworks.

Below is an example of an `.arcconfig` that runs both Ruby tests - with the [`RSpecTestEngine`](#rspec_test_engine) - and Python tests - with Arcanist's native `PytestTestEngine`:

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

You can also define some specific configuration for each engine. Below is an example that uses two [`TAPTestEngines`](#tap_test_engines) with different commands:

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
  "unit.engine.multi-test.engines": [
    "RSpecTestEngine",
    {
      "engine": "TAPTestEngine",
      "unit.engine.tap.command": "bundle exec teaspoon -f tap"
    },
    {
      "engine": "TAPTestEngine",
      "unit.engine.tap.command": "karma run spec/js/karma.conf"
    }
  ]
}
```

### `rspec_test_engine`

This extension allows you to run tests with [RSpec](http://rspec.info/) (version 2 or later). To use it, just set your test engine as `RSpecTestEngine`:

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

You can change the `rspec` executable path if it for some reason is not found in your `$PATH` variable (maybe you are using Bundler without binstubs):

```json
{
  "project_id": "my-awesome-project",
  "conduit_uri": "https://example.org",

  "load": [
    ".arcanist-extensions/rspec_test_engine"
  ],

  "unit.engine": "RSpecTestEngine",
  "unit.engine.rspec.command": "bundle exec rspec"
}
```

### `rubocop_linter`

This extension will lint your project using the awesome [Rubocop](https://github.com/bbatsov/rubocop) library. It is important to mention that the extension won't install Rubocop, so you must do it manually. Just make sure you have the `rubocop` executable listed on your `$PATH`.

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

If you need to customize the default style rules, just create a `.rubocop.yml` file on the root of your project as usual.

For more information regarding Arcanist linters configuration, access the [Arcanist Lint User Guide](https://secure.phabricator.com/book/phabricator/article/arcanist_lint/).

### `tap_test_engine`

This extension implements a generic [TAP](http://testanything.org/) test engine, so Arcanist may run tests from any tool that has a TAP compatible output.

To use this extension, you must inform the command that will run your tests (just make sure that this command returns a TAP formatted output on `STDOUT`):

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

### `eslint_linter`

This extension will lint your project using [ESlint](http://eslint.org). It is important to mention that the extension won't install ESlint, so you must do it manually. Just make sure you have the `eslint` executable listed on your `$PATH`.

Below is an example of an `.arclint` file that includes the ESlint Linter:

```json
{
  "linters": {
    "javascript": {
      "type": "eslint",
      "include": "/\\.js$/"
    }
  }
}
```

# License

(The MIT License)

Copyright (c) 2015 Tagview Tecnologia <team@tagview.com.br>

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
