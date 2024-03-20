Composer Downloads Plugin [![Build Status][actions_badge]][actions_link] [![Coverage Status][coveralls_badge]][coveralls_link] [![PHP Version][php-version-image]][php-version-url]
===========================

This plugin allows you to download extra files and extract them within your package.

This is an updated version of [civicrm/composer-downloads-plugin](https://github.com/civicrm/composer-downloads-plugin).
It adds support for more archive files and allow custom variables.

## Example

Suppose your PHP package `foo/bar` relies on an external archive file (`examplelib-1.1.0-windows-amd64.zip` on Windows, or `examplelib-1.1.0-linux-x86_64.tar.gz` on Linux, or `examplelib-1.1.0-darwin-x86_64.tar.gz` on MacOS):

```json
{
  "name": "foo/bar",
  "require": {
    "pact-foundation/composer-downloads-plugin": "^1.0"
  },
  "extra": {
    "downloads": {
      "examplelib": {
        "url": "https://example.com/examplelib-{$version}-{$os}-{$architecture}.{$extension}",
        "path": "extern/{$id}",
        "version": "1.1.0",
        "variables": {
            "{$os}": "strtolower(PHP_OS_FAMILY)",
            "{$architecture}": "strtolower(php_uname('m'))",
            "{$extension}": "PHP_OS_FAMILY === 'Windows' ? 'zip' : 'tar.gz'",
        },
        "ignore": ["tests", "doc", "*.md"],
        "hash": {
          "algo": "sha256",
          "value": "08fbce50f84d89fdf1fdef425c7dd1a13c5c023fa87f453ba77db4df27d273c0"
        }
      }
    }
  }
}
```

When a downstream user of `foo/bar` runs `composer require foo/bar`, it will download and extract the archive file to `vendor/foo/bar/extern/examplelib`. 

## Attribute:

* `url`: The URL to the extra file.

* `path`: The releative path where content will be extracted.

* `type`: (*Optional*) Determines how the download is handled. If omit, the extension in `url` will be used to detect.
    * Archive types (The archive file `url` will be downloaded and extracted to `path`):
      * `zip`: . Support extension `*.zip`
      * `rar`: Support extension `*.rar`
      * `xz`: Support extension `*.tar.xz`
      * `tar`: Support extensions `*.tar.gz`, `*.tar.bz2`, `*.tar`, `*.tgz`
    * File types (The file `url` will be downloaded and placed at `path`):
      * `file`
      * `phar`: The file will be mark as executable.
      * `gzip`: The `*.gz` file will be extracted to a file that will be placed at `path`.

* `ignore`: (*Optional*) A list of a files that should be omited from the extracted folder.
  * This supports a subset of `.gitignore` notation.
  * Only useful with archive types.

* `executable`: (*Optional*) Indicate list of files should be mark as executable.
  * For archive types: the value should be a list of extracted files
  * For file types: the value should be boolean (true/false)

* `version`: (*Optional*) A version number for the downloaded artifact.
  * This has no functional impact on the lifecycle of the artifact.
  * It can affect the console output.
  * It can be used as a variable.

* `variables`: (*Optional*) List of custom variables.

* `hash`: (*Optional*) Verify contents of the file downloaded from `url`. If hash values are not the same: file will be **deleted** & composer will **throw exception**.
  * `algo`: Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..). For a list of supported algorithms see `hash_algos()`
  * `value`: Expected value of hash function

## Variables

### Supported Attribute

Only following attribute support variables:

* `url`
* `path`
* `ignore`

### Default Variables

* `{$id}`: The identifier of the download. (In the example, it would be `examplelib`.)
* `{$version}`: Just a text defined in the `version` attribute, if not defined, the value will be empty string (`""`).

### Custom Variables

* The format will be `"{$variable-name}": "EXPRESSION-SYNTAX-EVALUATED-TO-STRING"`
* More about the syntax at [Expression Syntax](https://github.com/leongrdic/php-smplang#expression-syntax).
* The syntax must be evaluated into a `string`.

#### Methods

Custom variable support these methods:
* `range`
* `strtolower`
* `php_uname`
* `in_array`
* `str_contains`
* `str_starts_with`
* `str_ends_with`
* `matches`

#### Constants

Custom variable support these constants:
* `PHP_OS`
* `PHP_OS_FAMILY`
* `PHP_SHLIB_SUFFIX`
* `DIRECTORY_SEPARATOR`

## Default Attributes

You may set default attributes for all downloads. Place them under `*`, as in:

```json
{
  "extra": {
    "downloads": {
      "*": {
        "path": "bower_components/{$id}",
        "ignore": ["test", "tests", "doc", "docs"],
        "variables": {
          "{$extension}": "zip"
        }
      },
      "jquery": {
        "url": "https://github.com/jquery/jquery-dist/archive/1.12.4.{$extension}"
      },
      "jquery-ui": {
        "url": "https://github.com/components/jqueryui/archive/1.12.1.{$extension}"
      }
    }
  }
}
```

## Document

See more at [Doc](./doc/)

## Contributing

Pull requests are welcome, please [send pull requests](https://github.com/pact-foundation/composer-downloads-plugin/pulls).

If you found any bug, please [report issues](https://github.com/pact-foundation/composer-downloads-plugin/issues).

## Authors

* **Rob Bayliss** - [Composer Extra Files](https://github.com/LastCallMedia/ComposerExtraFiles/graphs/contributors)
* **Tim Otten** and contributors - [Composer Download Plugin](https://github.com/civicrm/composer-downloads-plugin/graphs/contributors)
* **Tien Vo** and contributors - [this project](https://github.com/pact-foundation/composer-downloads-plugin/graphs/contributors)

## License

This package is available under the [MIT license](LICENSE).

[actions_badge]: https://github.com/pact-foundation/composer-downloads-plugin/workflows/main/badge.svg
[actions_link]: https://github.com/pact-foundation/composer-downloads-plugin/actions

[coveralls_badge]: https://coveralls.io/repos/pact-foundation/composer-downloads-plugin/badge.svg?branch=main&service=github
[coveralls_link]: https://coveralls.io/github/pact-foundation/composer-downloads-plugin?branch=main

[php-version-url]: https://packagist.org/packages/pact-foundation/composer-downloads-plugin
[php-version-image]: http://img.shields.io/badge/php-8.0.0+-ff69b4.svg
