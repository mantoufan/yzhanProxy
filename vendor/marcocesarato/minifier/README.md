# PHP Minifier

**Version:** 0.1.5 beta

**Github:** https://github.com/marcocesarato/PHP-Minifier

**Author:** Marco Cesarato

## Description

This class can minify HTML, JavaScript and CSS to take less space.

It can take a string with either HTML, JavaScript or CSS, and parses it to eliminate unnecessary text.

The class returns as result a a string that is smaller than the original.

## Requirements

- php 4+

## Install

### Composer
1. Install composer
2. Type `composer require marcocesarato/minifier`
4. Enjoy

## Usage

```php
ob_start();

$html = <<<EOD
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    <h1>Hello World</h1>
</body>
</html>
EOD;

echo $html;

$content = ob_get_contents();
ob_clean();

$minifier = new Minifier();
$min_html = $minifier->minifyHTML($content);

echo $min_html;
```

## Methods

### Minifier

| Method      | Parameters                          | Description                                        |
| ----------- | ----------------------------------- | -------------------------------------------------- |
| minifyJS    |       $javascript<br>return string                              | Minify Javascript                               |
| minifyCSS      | 	  $css<br>return string | Minify CSS                           |
| minifyHTML      |   $html<br>return string  | Minify HTML |