# WP Cli 
This CLI Package Provides WP Theme / Plugin Developers To Add TextDomains Easily

[![Latest Stable Version][latest-stable-version-img]][latest-stable-version-link]
[![Latest Unstable Version][latest-Unstable-version-img]][latest-Unstable-version-link]
[![Total Downloads][total-downloads-img]][total-downloads-link]
[![WP][wpcs-img]][wpcs-link]
[![License][license-img]][license-link]
[![composer.lock available][composerlock-img]][composerlock-link]

## Installation
The preferred way to install this extension is through [Composer][composer].

To install **WP-Cli library**, simply:

    $ composer require varunsridharan/wp-cli-textdomain
    
## Add Text Domain Usage

### Update Textdomains for all files inside a folder
```
$ ./vendor/bin/add-text-domain.bat -i your-textdomain your-folder-path
```

### Update Textdomains for a file
```
$ ./vendor/bin/add-text-domain.bat -i your-textdomain ./your-path/your-file.php
```

## CLI Arguments
| Argument | Description |
| -------- | ----------- |
| `-i` | if provided then changes will be saved in the same file where its updated if not it will provide the file output | 


## Make Pot Usage
#### CMD
```
$ ./vendor/bin/makepot.bat ./pot-config.json
```
#### JSON File `pot-config.json`
```json
{
  "src"     : "./",
  "dist"    : "languages/plugin-slug.pot",
  "domain"  : "project-text-domain",
  "exclude" : "comma seperated folders name",
  "headers" : {
	"Last-Translator" : "Author Name\n",
	"Language-Team"   : "LANGUAGE ll@example.com"
  }
}
```

---

<!-- START common-footer.mustache  -->
## 📝 Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

[Checkout CHANGELOG.md](https://github.com/varunsridharan/wp-cli-textdomain/blob/main/CHANGELOG.md)


## 🤝 Contributing
If you would like to help, please take a look at the list of [issues](https://github.com/varunsridharan/wp-cli-textdomain/issues/).


## 📜  License & Conduct
- [**GNU General Public License v3.0**](https://github.com/varunsridharan/wp-cli-textdomain/blob/main/LICENSE) © [Varun Sridharan](website)
- [Code of Conduct](https://github.com/varunsridharan/.github/blob/main/CODE_OF_CONDUCT.md)


## 📣 Feedback
- ⭐ This repository if this project helped you! :wink:
- Create An [🔧 Issue](https://github.com/varunsridharan/wp-cli-textdomain/issues/) if you need help / found a bug


## 💰 Sponsor
[I][twitter] fell in love with open-source in 2013 and there has been no looking back since! You can read more about me [here][website].
If you, or your company, use any of my projects or like what I’m doing, kindly consider backing me. I'm in this for the long run.

- ☕ How about we get to know each other over coffee? Buy me a cup for just [**$9.99**][buymeacoffee]
- ☕️☕️ How about buying me just 2 cups of coffee each month? You can do that for as little as [**$9.99**][buymeacoffee]
- 🔰         We love bettering open-source projects. Support 1-hour of open-source maintenance for [**$24.99 one-time?**][paypal]
- 🚀         Love open-source tools? Me too! How about supporting one hour of open-source development for just [**$49.99 one-time ?**][paypal]

<!-- Personl Links -->
[paypal]: https://sva.onl/paypal
[buymeacoffee]: https://sva.onl/buymeacoffee
[twitter]: https://sva.onl/twitter/
[website]: https://sva.onl/website/


## Connect & Say 👋
- **Follow** me on [👨‍💻 Github][github] and stay updated on free and open-source software
- **Follow** me on [🐦 Twitter][twitter] to get updates on my latest open source projects
- **Message** me on [📠 Telegram][telegram]
- **Follow** my pet on [Instagram][sofythelabrador] for some _dog-tastic_ updates!

<!-- Personl Links -->
[sofythelabrador]: https://www.instagram.com/sofythelabrador/
[github]: https://sva.onl/github/
[twitter]: https://sva.onl/twitter/
[telegram]: https://sva.onl/telegram/


---

<p align="center">
<i>Built With ♥ By <a href="https://sva.onl/twitter"  target="_blank" rel="noopener noreferrer">Varun Sridharan</a> <a href="https://en.wikipedia.org/wiki/India">
   <img src="https://cdn.svarun.dev/flag-india.jpg" width="20px"/></a> </i> <br/><br/>
   <img src="https://cdn.svarun.dev/codeispoetry.png"/>
</p>

---


<!-- END common-footer.mustache  -->

[composer]: http://getcomposer.org/download/
[downloadzip]:https://github.com/varunsridharan/wp-cli-textdomain/archive/master.zip

[latest-stable-version-img]: https://poser.pugx.org/varunsridharan/wp-cli-textdomain/version
[latest-Unstable-version-img]: https://poser.pugx.org/varunsridharan/wp-cli-textdomain/v/unstable
[total-downloads-img]: https://poser.pugx.org/varunsridharan/wp-cli-textdomain/downloads
[Latest-Unstable-version-img]: https://poser.pugx.org/varunsridharan/wp-cli-textdomain/v/unstable
[wpcs-img]: https://img.shields.io/badge/WordPress-Standar-1abc9c.svg
[license-img]: https://poser.pugx.org/varunsridharan/wp-cli-textdomain/license
[composerlock-img]: https://poser.pugx.org/varunsridharan/wp-cli-textdomain/composerlock

[latest-stable-version-link]: https://packagist.org/packages/varunsridharan/wp-cli-textdomain
[latest-Unstable-version-link]: https://packagist.org/packages/varunsridharan/wp-cli-textdomain
[total-downloads-link]: https://packagist.org/packages/varunsridharan/wp-cli-textdomain
[Latest-Unstable-Version-link]: https://packagist.org/packages/varunsridharan/wp-cli-textdomain
[wpcs-link]: https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/
[license-link]: https://packagist.org/packages/varunsridharan/wp-cli-textdomain
[composerlock-link]: https://packagist.org/packages/varunsridharan/wp-cli-textdomain
