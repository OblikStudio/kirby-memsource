# kirby-memsource
A plugin for [Kirby CMS](https://getkirby.com/) that can be used to export the site's content and create tasks (a.k.a. jobs) in [Memsource](https://www.memsource.com/). After the tasks are done and the content is translated, it can be imported back in Kirby.

## Demo
<img src="https://i.imgur.com/vJeP8Jq.gif" alt="kirby-memsource demo"/><br>
<img src="https://i.imgur.com/y30SlJw.png" alt="Memsource app with opened translation job" width="608"/>
<img src="https://i.imgur.com/ytG767f.jpg" alt="Kirby Starterkit in English" width="304"/><img src="https://i.imgur.com/qIaytOw.jpg" alt="Kirby Starterkit in Bulgarian" width="304"/>

## Features
- Very simple workflow.
- Blueprint interpretation. During export, the plugin will parse your blueprints and ignore fields with `translate: false`.
- Supports [language variables](https://getkirby.com/docs/languages/variables) in YAML format.
- Turns kirbytags to Memsource tags, which means that this:

  ```
  © 2009–(date: Year) (link: http://getkirby.com text: The Kirby Team)
  ```
  
  is converted to this during the export:
  
  ```
  © 2009–<kirby date="Year" /> <kirby link="http://getkirby.com">The Kirby Team</kirby>
  ```
  
  which Memsource interprets as (can be seen in the Memsource app screenshot above):
  
  ```
  © 2009–[1] [2]The Kirby Team[3]
  ```
  
  This basically opens the `text` property of a kirbytag to be translated by the linguists, while keeping other unrelated information safe. After the import, the modified kirby tag is turned back to its original format:
  
  ```
  © 2009–(date: Year) (link: http://getkirby.com text: Екипа на Кирби)
  ```

## Installation
1. Open your Kirby project in a terminal.
2. Navigate to the `site/plugins/` folder.
3. `git submodule add https://github.com/OblikStudio/kirby-memsource memsource`.
4. Make sure your site is multilingual by changing the `config/config.php` file. More info [here](https://getkirby.com/docs/languages/setup).

## Usage
1. Sign up for a [Memsource developer account](https://cloud.memsource.com/web/organization/signup?e=DEVELOPER).
2. Create a new Project in your Memsource account. Set the source and target language to match your project's languages defined in `config.php`. Make sure that the `locale` attribute of each language matches the locale set in Memsource. For example, if the Memsource language is "English (United States)", the language definition in your `config.php` should have `'locale' => 'en_US'`. If the language is just "English", you should have `'locale' => 'en'`.
3. Open the Kirby panel of your project.
4. In the plugin's widget, sign in your Memsource account.
5. Select the Memsource project you created earlier.
6. Export your site's content by clicking "Export" and then upload it to Memsource by clicking "Upload". This will create a new Job in Memsource.
7. Open the newly created Job in the Memsource web app.
8. Translate the content.
9. Head back to the widget and open the Memsource project.
10. Click on the Job which you translated.
11. Click "Import" to update your site's content.
12. Your site is translated.

## Tips
- You can open the `/panel/memsource/export` route of your project in a browser to see your site's exported content in JSON format.

## Notes
- This plugin is used for the translation of a fairly large website and it has worked flawlessly so far. The localization team is also very happy with the results.
- There is currently no support for the translation of files (their alt text, for example). This might be added at some point in the future.
