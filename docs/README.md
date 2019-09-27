![Memsource integration for Kirby](visual.png)

This plugin allows you to translate your entire site content in the powerful TMS [Memsource](https://www.memsource.com/). Features:

- Create Memsource jobs with great control over what's exported
- Import Memsource jobs with reports for what has changed
- Functionality to capture the state of your site and use it as a reference so you can later export only the differences
- Support for the [Kirby Editor](https://github.com/getkirby/editor)
- Great control over the exported format of fields via [kirby-outsource](https://github.com/OblikStudio/kirby-outsource)
- Functionality to translate language variables via [kirby-variables](https://github.com/OblikStudio/kirby-variables)

<div style="display: flex">
<div>
Exporting content:

![export demo](export.gif)
</div>
<div>
Importing content:

![import demo](import.gif)
</div>
</div>

## Installation

With [Composer](https://packagist.org/packages/oblik/kirby-memsource):

```
composer require oblik/kirby-memsource
```

[Sign up](https://cloud.memsource.com/web/organization/signup?e=DEVELOPER) for a developer account in Memsource.

## Usage

You can specify how each filed type should be exported for translation. For example, if you have a field formatted in YAML, you can specify that YAML should be parsed on export and encoded on import like this:

```yml
fields:
  data:
    type: myfield
    outsource:
      serialize:
        yaml: true
```

If you don't want to specify that for each occurrence of the `myfield` field type, use _config.php_:

```php
return [
    'oblik.memsource.fields' => [
        'myfield' => [
            'serialize' => [
                'yaml' => true
            ]
        ]
    ]
];
```

For more information on that, refer to the [kirby-outsource](https://github.com/OblikStudio/kirby-outsource#field-settings) documentation.
