<?php
namespace Memsource;

class Exporter {
  public $language = null;

  public function isFieldTranslatable ($blueprint) {
    $isTranslate = $blueprint['translate'] ?? true;
    $isFiles = $blueprint['type'] === 'files';
    $isPages = $blueprint['type'] === 'pages';

    return (
      $isTranslate &&
      !$isFiles &&
      !$isPages
    );
  }

  public function extractFieldData ($blueprint, $input) {
    if (!$this->isFieldTranslatable($blueprint)) {
      return null;
    }

    $isFieldInstance = is_object($input);

    if ($blueprint['type'] === 'structure') {
      $data = [];
      $content = $isFieldInstance ? $input->yaml() : $input;

      foreach ($content as $index => $entry) {
        $childData = [];

        foreach ($blueprint['fields'] as $fieldName => $fieldBlueprint) {
          $fieldKey = strtolower($fieldName);

          if (isset($entry[$fieldKey])) {
            $fieldValue = $entry[$fieldKey];
            $extractedValue = $this->extractFieldData($fieldBlueprint, $fieldValue);

            if (!empty($extractedValue)) {
              $childData[$fieldName] = $extractedValue;
            }
          }
        }

        if (!empty($childData)) {
          $data[$index] = $childData;
        }
      }
    } else {
      $data = $isFieldInstance ? $input->value() : $input;
    }

    return $data;
  }

  public function extractEntity ($entity) {
    $data = [];
    $content = $entity->content($this->language);
    $fieldBlueprints = $entity->blueprint()->fields();

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

    return $data;
  }

  public function extractPageContent ($page) {
    $data = $this->extractEntity($page);
    $files = [];

    foreach ($page->files() as $file) {
      $fileData = $this->extractEntity($file);

      if (!empty($fileData)) {
        $files[$file->id()] = $fileData;
      }
    }

    return [
      'content' => $data,
      'files' => $files
    ];
  }

  public function export ($language = null) {
    $pages = [];
    $files = [];

    $siteData = $this->extractPageContent(site());
    $files = array_replace($files, $siteData['files']);

    foreach (site()->index() as $page) {
      $pageData = $this->extractPageContent($page);

      if (!empty($pageData['content'])) {
        $pages[$page->id()] = $pageData['content'];
      }

      $files = array_replace($files, $pageData['files']);
    }

    return [
      'site' => $siteData['content'],
      'pages' => $pages,
      'files' => $files
    ];
  }
}
