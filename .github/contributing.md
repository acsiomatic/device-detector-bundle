# Contributing

First, thank you for reaching here.

## Issues

If you want to report bugs, ask for enhancements, or other requests, [open an issue].

## Contributing with code

### Requirements

- [Git]
- [Make]
- [Podman] (or [Docker])

### Usual workflow

1. [Fork this project]
1. Clone the fork to your machine
    ```bash
    git clone git@github.com:YOUR_USERNAME/device-detector-bundle.git
    ```
1. Get inside the project
    ```bash
    cd device-detector-bundle
    ```
1. Check it
    ```bash
    make check
    ```
    - The `check` recipe will run [static analysis, coding standards checks, and tests](#makefile-recipes).
    - If something goes wrong, please, [open an issue].
1. [Create a branch]
1. Do your magic
1. Check your changes
    ```bash
    make check
    ```
    - The `check` recipe will run [static analysis, coding standards checks, and tests](#makefile-recipes).
    - You might [check how things go in a real application](#try-changes-in-a-real-application).
    - You might [run tests against some specific PHP version](#run-tests-against-some-specific-php-version).
1. [Create a pull request]

### Try changes in a real application

Assuming you have both projects (a Symfony application, and a fork of this bundle) installed in you machine, you can try your changes in a real application.

Follow the steps described in [usual workflow](#usual-workflow) section, then setup [path Composer repository].

> (...) you can use the path (repository) one, which allows you to depend on a local directory, either absolute or relative.

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../../path-to-your-fork/device-detector-bundle"
        }
    ],
    "require": {
        "acsiomatic/device-detector-bundle": "*"
    }
}
```

Don't forget to update (or require) the bundle in the application:

```bash
composer update acsiomatic/device-detector-bundle
```

### Run tests against some specific PHP version

```bash
make clean
PHP_VERSION=7.3 make test
```

### Makefile recipes

Here is the list of recipes:

```bash
make help
```

```
Usage:
  [PHP_VERSION=major.minor] make [target]

Available targets:
  help                Display this message help
 Checks
  check               Run all checks
  static-analysis     Run static analysis
  cs-check            Check for coding standards violations
  test                Run tests
 Fixers
  cs-fix              Fix coding standards
 Misc
  clean               Clean up workspace
```

[create a branch]: https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-and-deleting-branches-within-your-repository
[create a pull request]: https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork
[docker]: https://www.docker.com/
[fork this project]: https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/working-with-forks
[git]: https://git-scm.com/
[make]: https://www.gnu.org/software/make/
[open an issue]: https://docs.github.com/en/free-pro-team@latest/github/managing-your-work-on-github/creating-an-issue
[path Composer repository]: https://getcomposer.org/doc/05-repositories.md#path
[podman]: https://podman.io/
