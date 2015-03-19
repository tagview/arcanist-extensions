# Configuring lints

This document will cover how to add lint checking inside
different text editors. If you use a different editor that
isn't covered here then place take a few minutes to add it.

## Vim

Install [syntastic](https://github.com/scrooloose/syntastic)
and in your .vimrc add

``` vimscript
let g:syntastic_ruby_checkers = ['rubocop']
```

Open a ruby file and write it straight away. If the file has
any rubocop offenses it should display arrows in the gutter.
See the syntastic docs for examples.


## Emacs

## Sublime

