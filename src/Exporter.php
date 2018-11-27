<?php
namespace Memsource;

use Yaml;

class Exporter {
    public static $ignored_fields = ['line', 'tabs', 'image', 'date', 'toggle', 'headline'];

    public $data = [];
    public $alerts = [];

    private static function isFieldTranslatable ($field) {
        if (isset($field['translate']) && $field['translate'] == false) {
            return false;
        }

        if (isset($field['type']) && in_array($field['type'], static::$ignored_fields)) {
            return false;
        }

        return true;
    }

    private function blueprintTranslatableFields ($data, &$result = []) {
        if (!empty($data['fields'])) {
            foreach ($data['fields'] as $key => $value) {
                $fieldId = str_replace(array('-', ' '), '_', strtolower(trim($key))); // from Kirby core
                $isTranslatable = static::isFieldTranslatable($value);

                if ($isTranslatable && !empty($value['fields'])) {
                    if (!empty($value['type']) && $value['type'] === 'structure') {
                        // Store `structure` fields in a new array.
                        $result[$fieldId] = $this->blueprintTranslatableFields($value);
                    } else {
                        // Store primitive fields in the flattened array.
                        $this->blueprintTranslatableFields($value, $result);
                    }
                } else {
                    $result[$fieldId] = $isTranslatable;
                }
            }
        }

        return $result;
    }

    private function exportObject ($fields, $translatable, $id) {
        $data = [];

        foreach ($translatable as $key => $value) {
            if (isset($fields[$key])) {
                if (is_array($value)) {
                    // The translatable map is an array, so this field should
                    // be a structure that can be parsed to an array.

                    $data[$key] = [];
                    $fieldData = $fields[$key]->yaml();

                    foreach ($fieldData as $entry) {
                        $exportedChild = $this->exportObject($entry, $value, "$id:$key");

                        if (!empty($exportedChild)) {
                            array_push($data[$key], $exportedChild);
                        }
                    }
                    
                    // If the field has no children or there are no
                    // translatable fields, remove the empty array.
                    if (empty($data[$key])) {
                        unset($data[$key]);
                    }                    
                } else if ($value === true) {
                    if (method_exists($fields[$key], 'value')) {
                        $fieldValue = $fields[$key]->value(); // if the value is a Kirby Field object
                    } else {
                        $fieldValue = $fields[$key]; // if the value is primitive
                    }

                    if (json_encode($fieldValue)) {
                        $data[$key] = $fieldValue;
                    } else {
                        array_push($this->alerts, [
                            'type' => 'warning',
                            'text' => "Could not encode: $id:$key"
                        ]);
                    }
                }
            }
        }

        return $data;
    }

    public function exportPage ($page, &$data = []) {
        $reader = new BlueprintReader;
        $id = $page->isSite() ? '$site' : $page->id();

        if (!empty($id)) {
            $blueprint = $reader->get($page->template());
            $translatable = $this->blueprintTranslatableFields($blueprint);
            $data[$id] = $this->exportObject($page->content()->data(), $translatable, $id);
        }

        $children = $page->children();
        if (count($children) > 0) {
            foreach ($children as $key => $child) {
                $this->exportPage($child, $data);
            }
        }
        
        return $data;
    }

    public function exportLanguageVars () {
        $file = kirby()->roots()->languages() . DS . $this->lang . '.yml';

        if (file_exists($file)) {
            $contents = file_get_contents($file);
            return Yaml::read($contents);
        } else {
            return null;
        }   
    }

    public function export () {
        $this->lang = site()->language()->code();

        $pages = $this->exportPage(site());
        $vars = $this->exportLanguageVars();

        if (count($pages) > 0) {
            $this->data['pages'] = $pages;
        }

        if ($vars) {
            $this->data['variables'] = $vars;
        }

        return $this->data;
    }
}
