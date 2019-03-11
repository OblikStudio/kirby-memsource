<?php
namespace Memsource;

class Exporter {
  public $language = null;

  public function extractFieldData ($blueprint, $input) {
    if (isset($blueprint['translate']) && $blueprint['translate'] == false) {
      return null;
    }

    $isFieldInstance = is_object($input);

    if ($blueprint['type'] === 'structure') {
      $data = [];
      $content = $isFieldInstance ? $input->yaml() : $input;

      foreach ($content as $index => $entry) {
        $childData = [];

        foreach ($blueprint['fields'] as $name => $field) {
          $fieldValue = $this->extractFieldData(
            $field,
            $entry[strtolower($name)]
          );

          if (!empty($fieldValue)) {
            $childData[$name] = $fieldValue;
          }
        }

        if (!empty($childData)) {
          $data[$index] = $childData;
        }
      }

      return $data;
    } else {
      return $isFieldInstance ? $input->value() : $input;
    }
  }

  public function extractPageContent ($page) {
    $data = [];
    $files = [];

    $content = $page->content($this->language);
    $fieldBlueprints = $page->blueprint()->fields();

    foreach ($fieldBlueprints as $fieldName => $fieldBlueprint) {
      $field = $content->$fieldName();
      $fieldData = $this->extractFieldData(
        $fieldBlueprint,
        $field
      );

      if (!empty($fieldData)) {
        $data[$fieldName] = $fieldData;
      }
    }

    foreach ($page->files() as $file) {
      $files[$file->id()] = $file->content($this->language)->toArray();
    }

    return [
      'data' => $data,
      'files' => $files
    ];
  }

  public function export ($language = null) {
    $pages = [];
    $files = [];

    $site = $this->extractPageContent(site());

    foreach (site()->index() as $page) {
      $pages[$page->id()] = $page->content($language)->toArray();

      foreach ($page->files() as $file) {
        $files[$file->id()] = $file->content($language)->toArray();
      }
    }

    return [
      'site' => $site,
      'pages' => $pages,
      'files' => $files
    ];
  }
}
