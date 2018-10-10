# Arcanist Extensions

This project provides some useful extensions for [Arcanist](https://github.com/phacility/arcanist).

Note: the rubocop extension requires rubocop v0.50.* or above.

## Extensions

- [Multi Test Engine](#multi_test_engine)
- [RSpec Test Engine](#rspec_test_engine)
- [Rubocop Linter](#rubocop_linter)
- [TAP Test Engine](#tap_test_engine)
- [SCSS-Lint Linter](#scss_lint_linter)
- [ESlint Linter](#eslint_linter)
- [Prettier Linter](#prettier_linter)
- [markdownlint Linter](#markdownlint_linter)

## Installation

The easiest way to use any of these extensions on your own project, is by adding this repository as a git submodule, given you are using git (which you obviously should):

```
$ git submodule add https://github.com/bitnami/arcanist-extensions.git .arcanist-extensions
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

### `scss_lint_linter`

This extension will lint your project using the awesome [SCSS-Lint](https://github.com/causes/scss-lint) library. It is important to mention that the extension won't install scss-lint, so you must do it manually. Just make sure you have the `scss-lint` executable listed on your `$PATH`.

Below is an example of an `.arclint` file that includes the SCSS-Lint Linter:

```json
{
  "linters": {
    "scss-lint": {
      "type": "scss-lint",
      "include": "/\\.(scss)$/"
    }
  }
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

### `prettier_linter`

This extension will format your JS code using [Prettier](http://eslint.org). It is important to mention that the extension won't install `prettier`, so you must do it manually. Just make sure you have the `prettier` executable added to the `package.json` of your project and available in the `./node_modules` folder.

Below is an example of an `.arclint` file that includes the Prettier Linter:

```json
{
  "linters": {
    "prettier": {
      "type": "prettier",
      "include": "(\\.js$)",
      "bin": "./node_modules/.bin/prettier",
      "exclude": [
        "(^node_modules/)",
        "(^build/)",
        "(^dist/)",
        "(^out/)"
      ],
      "prettier.cwd": "./"
    }
  }
}
```

### `markdownlint_linter`

This extension will lint your project using [markdownlint](https://github.com/markdownlint/markdownlint). It is important to mention that the extension won't install mdl, so you must do it manually (`gem install mdl`). Just make sure you have the `mdl` executable listed on your `$PATH`.

Below is an example of an `.arclint` file that includes the markdownlint Linter:

```json
{
  "linters": {
    "markdown": {
      "type": "markdownlint",
      "include": "/\\.(md|markdown)/",
      "mdl.config": ".mdlrc.cfg"
    }
  }
}
```

For more information regarding Arcanist linters configuration, access the [Arcanist Lint User Guide](https://secure.phabricator.com/book/phabricator/article/arcanist_lint/).
