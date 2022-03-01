# Dravencms html snippet module

This is a simple html snippet module for dravencms

## Instalation

The best way to install dravencms/html-snippet is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/html-snippet
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
    htmlSnippet: Dravencms\HtmlSnippet\DI\HtmlSnippetExtension
```
