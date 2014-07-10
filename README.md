Arcanist Extensions
===================

Add this project as a submodule of the desired project.

`git submodule add git@github.com:tagview/arcanist-extensions.git .arcanist-extensions`

Then, load the desired extension into your project.

```json
  "load": [
    ".arcanist-extensions/[extension_name]"
  ]
```

For now, the only extension available is **rubocop_linter**.
