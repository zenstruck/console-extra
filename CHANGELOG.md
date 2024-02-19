# CHANGELOG

## [v1.4.0](https://github.com/zenstruck/console-extra/releases/tag/v1.4.0)

February 19th, 2024 - [v1.3.3...v1.4.0](https://github.com/zenstruck/console-extra/compare/v1.3.3...v1.4.0)

* 40fe882 feat: configure auto-completion suggestions with attributes (#62) by @kbond
* 58dce00 feat: allow extending `Option`/`Argument` attributes (#62) by @kbond
* 48a75df doc: update docs (#62) by @kbond
* 03bd8c6 minor: trigger deprecation note when using traits when not required (#62) by @kbond
* b2c5711 feat: add `InvokableCommand` base class (#62) by @kbond
* 7d88fea minor: deprecate defaulting arguments/options to camelCase (#62) by @kbond
* e37f3c7 minor: remove symfony/string from require (#62) by @kbond
* 55486e7 feat: require PHP 8.1+ and Symfony 6.4+ (#62) by @kbond
* e5308d7 minor: add `@deprecation` annotations (#62) by @kbond
* b4ddcd6 minor: reset before tests (#62) by @kbond

## [v1.3.3](https://github.com/zenstruck/console-extra/releases/tag/v1.3.3)

February 14th, 2024 - [v1.3.2...v1.3.3](https://github.com/zenstruck/console-extra/compare/v1.3.2...v1.3.3)

* 4053813 fix: don't autowire `Option|Attribute` (#61) by @kbond

## [v1.3.2](https://github.com/zenstruck/console-extra/releases/tag/v1.3.2)

February 14th, 2024 - [v1.3.1...v1.3.2](https://github.com/zenstruck/console-extra/compare/v1.3.1...v1.3.2)

* 70e4b6a fix: autowiring non-service arguments (#60) by @kbond

## [v1.3.1](https://github.com/zenstruck/console-extra/releases/tag/v1.3.1)

October 25th, 2023 - [v1.3.0...v1.3.1](https://github.com/zenstruck/console-extra/compare/v1.3.0...v1.3.1)

* e63454e fix: Update composer.json to allow symfony 7 for string component (#58) by @tacman

## [v1.3.0](https://github.com/zenstruck/console-extra/releases/tag/v1.3.0)

October 24th, 2023 - [v1.2.0...v1.3.0](https://github.com/zenstruck/console-extra/compare/v1.2.0...v1.3.0)

* 2710f11 feat: Symfony 7 support (#57) by @kbond

## [v1.2.0](https://github.com/zenstruck/console-extra/releases/tag/v1.2.0)

March 23rd, 2023 - [v1.1.0...v1.2.0](https://github.com/zenstruck/console-extra/compare/v1.1.0...v1.2.0)

* d5737dc feat: allow DI attributes for `__invoke()` parameters (#44) by @kbond
* 8f6b2de feat: deprecate `AutoName` (#47) by @kbond
* 972a621 feat: require php 8+, symfony 5.4+ (#47) by @kbond
* fee7613 fix(ci): add token by @kbond
* 5958c69 fix: `getSubscribedServices()` can return `SubscribedService[]` by @kbond
* 746a378 chore(ci): fix by @kbond
* cb1c789 fix: ci (#46) by @kbond
* 5fe180f chore: update ci config (#45) by @kbond
* 100885c ci: fix (#42) by @kbond
* ede0130 minor: adjust min symfony/string req (#42) by @kbond

## [v1.1.0](https://github.com/zenstruck/console-extra/releases/tag/v1.1.0)

July 12th, 2022 - [v1.0.1...v1.1.0](https://github.com/zenstruck/console-extra/compare/v1.0.1...v1.1.0)

* 04f4309 [minor] deprecate `ConfigureWithDocblocks` (#30) by @kbond
* edc10ea [minor] rename tests (#30) by @kbond
* dedbab2 [feature] define opts/args on `__invoke()` params with `Argument|Option` (#27) by @kbond
* 7717dad [minor] cs fix by @kbond
* a1e132f [feature] allow injecting args/options into `__invoke()` (#25) by @kbond

## [v1.0.1](https://github.com/zenstruck/console-extra/releases/tag/v1.0.1)

May 26th, 2022 - [v1.0.0...v1.0.1](https://github.com/zenstruck/console-extra/compare/v1.0.0...v1.0.1)

* 09b1f70 [minor] move setting Invokable::$io to initialize method by @kbond
* d6ebf15 [doc] fix `CommandSummarySubscriber` config by @kbond
* 0ca67cc [minor] use `Required` attribute when possible by @kbond
* 27c1fa1 [minor] support Symfony 6.1 (#24) by @kbond

## [v1.0.0](https://github.com/zenstruck/console-extra/releases/tag/v1.0.0)

April 8th, 2022 - _[Initial Release](https://github.com/zenstruck/console-extra/commits/v1.0.0)_
