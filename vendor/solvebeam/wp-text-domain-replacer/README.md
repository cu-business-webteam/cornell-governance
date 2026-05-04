# SolveBeam WordPress Text Domain Replacer

A developer tool for replacing WordPress translation text domains in PHP, JavaScript, and `block.json` files.

[![Latest Stable Version](https://img.shields.io/packagist/v/solvebeam/wp-text-domain-replacer?style=flat-square)](https://packagist.org/packages/solvebeam/wp-text-domain-replacer)
[![Total Downloads](https://img.shields.io/packagist/dt/solvebeam/wp-text-domain-replacer?style=flat-square)](https://packagist.org/packages/solvebeam/wp-text-domain-replacer)
[![License](https://img.shields.io/packagist/l/solvebeam/wp-text-domain-replacer?style=flat-square)](https://packagist.org/packages/solvebeam/wp-text-domain-replacer)

## Table of contents

- [Why This Tool Exists](#why-this-tool-exists)
- [Getting Started](#getting-started)
  - [Installation](#installation)
  - [First Run](#first-run)
- [Command Line Usage](#command-line-usage)
  - [Options](#options)
  - [Examples](#examples)
- [Configuration via composer.json](#configuration-via-composerjson)
- [Alternatives](#alternatives)
- [Links](#links)

## Why This Tool Exists

This tool was created for a common WordPress development workflow: reusing existing plugins or libraries inside another plugin or theme.

A well-known example is [Action Scheduler](https://actionscheduler.org/), which explicitly states:

> "Action Scheduler is designed to be used and released in plugins."

That is exactly where translation issues can appear.

When you embed a reusable library, it often keeps its own text domain (for example `action-scheduler`).
If your plugin uses another text domain (for example `my-plugin`), WordPress will not automatically treat those strings as part of your plugin's translations.

In practice, this means your plugin can be translated, but embedded library strings may remain untranslated for users.

This package solves that build-time problem by replacing the embedded library text domain with your plugin's text domain across your source files.
For example:

```bash
vendor/bin/wptdr --dir=src --search=action-scheduler --replace=my-plugin
```

After this replacement, all translatable strings consistently use your plugin's domain, so they can be handled in one translation flow.
This is especially useful for teams distributing plugins/themes that bundle reusable WordPress components.

## Getting Started

### Installation

To start replacing text domains in your WordPress plugin or theme, require SolveBeam WordPress Text Domain Replacer in Composer:

```bash
composer require solvebeam/wp-text-domain-replacer --dev
```

### First Run

To replace text domains in your project, use the `wptdr` command from the `vendor/bin/` directory:

```bash
vendor/bin/wptdr \
  --dir=src \
  --search=old_domain \
  --replace=new_domain
```

This will scan your `src` directory for any occurrence of the text domain `old_domain` in PHP files, JavaScript files, and `block.json` files, and replace them with `new_domain`.

## Command Line Usage

### Options

#### `--dir=DIRECTORY`

Specifies the directory to scan for text domain replacements. Defaults to the current working directory if not provided.

**Example:** `--dir=src` or `--dir=.`

#### `--search=DOMAIN`

The text domain to search for. You can specify multiple search domains by repeating this option.

**Example:** `--search=old_domain` or `--search=old_domain --search=legacy_domain`

#### `--replace=DOMAIN`

The text domain to replace with. This is required.

**Example:** `--replace=new_domain`

#### `--exclude=PATTERN`

Exclude directories or files by name. You can specify multiple patterns by repeating this option.

**Example:** `--exclude=node_modules --exclude=vendor`

### Examples

**Basic replacement in current directory:**

```bash
vendor/bin/wptdr --search=old_domain --replace=new_domain
```

**Replace in specific directory:**

```bash
vendor/bin/wptdr --dir=src --search=old_domain --replace=new_domain
```

**Replace multiple text domains:**

```bash
vendor/bin/wptdr \
  --dir=src \
  --search=old_domain \
  --search=legacy_domain \
  --replace=new_domain
```

**Replace while excluding directories:**

```bash
vendor/bin/wptdr \
  --dir=. \
  --search=old_domain \
  --replace=new_domain \
  --exclude=vendor \
  --exclude=node_modules
```

**Add to Composer scripts:**

```json
{
  "scripts": {
    "update-text-domain": "wptdr --dir=src --search=old_domain --replace=new_domain"
  }
}
```

Then run with:

```bash
composer update-text-domain
```

## Configuration via composer.json

Instead of passing all options via command line, you can also configure the text domain replacer in your `composer.json` file. This is useful for keeping your configuration in one place and simplifying your Composer scripts.

### Setup

Add a new `extra` section to your `composer.json`:


```json
{
  "extra": {
    "solvebeam-wp-text-domain-replacer": {
      "dir": "src",
      "search": [
        "old_domain",
        "legacy_domain"
      ],
      "replace": "new_domain",
      "exclude": [
        "vendor",
        "node_modules"
      ]
    }
  }
}
```

### Running with Configuration

Once configured in `composer.json`, you can run the tool without any options:

```bash
vendor/bin/wptdr
```

The tool will automatically load the configuration from `composer.json` and apply it.

### Overriding Configuration

Command-line options always take precedence over `composer.json` configuration. This allows you to override specific settings when needed:

```bash
# Use composer.json config but override the directory
vendor/bin/wptdr --dir=tests

# Use composer.json config but override the replacement domain
vendor/bin/wptdr --replace=another_domain

# Use composer.json config but replace different domains
vendor/bin/wptdr --search=other_domain
```

## Alternatives

Here is a list of alternatives that we found:

- [Automattic Babel Plugin Replace TextDomain](https://www.npmjs.com/package/@automattic/babel-plugin-replace-textdomain)
- [wp-textdomain](https://www.npmjs.com/package/wp-textdomain)
- [wp-cli-textdomain](https://github.com/varunsridharan/wp-cli-textdomain)
- [WordPress Coding Standards I18n Text Domain Fixer](https://github.com/WordPress/WordPress-Coding-Standards/blob/trunk/WordPress/Sniffs/Utils/I18nTextDomainFixerSniff.php)
- [WooCommerce TextDomain Script](https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/bin/package-update-textdomain.js)

## npm

- https://www.npmjs.com/package/@automattic/babel-plugin-replace-textdomain
- https://www.npmjs.com/package/wp-textdomain

## Links

- https://github.com/Automattic/babel-plugin-replace-textdomain
- https://github.com/timelsass/wp-textdomain
- https://github.com/varunsridharan/wp-cli-textdomain
- https://github.com/woocommerce/woocommerce/blob/9.8.5/plugins/woocommerce/bin/package-update-textdomain.js
- https://github.com/wp-cli/i18n-command
- https://github.com/WordPress/WordPress-Coding-Standards/blob/3.1.0/WordPress/Sniffs/Utils/I18nTextDomainFixerSniff.php
- https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#wordpressutilsi18ntextdomainfixer-replace-a-text_domain
- https://github.com/WordPress/gutenberg/blob/trunk/docs/how-to-guides/internationalization.md
