<?php

namespace Oblik\Memsource;

use Exception;
use Kirby\Toolkit\F;

class Snapshot {
    public static function file(string $name)
    {
        $folder = option('oblik.memsource.snapshots');
        return "$folder/$name.json";
    }

    public static function getAll()
    {
        return glob(self::file('*'));
    }

    public static function create(string $name, array $data)
    {
        $file = self::file($name);

        if (!F::exists($file)) {
            return F::write($file, json_encode($data));
        } else {
            throw new Exception('Snapshot already exists', 400);
        }
    }

    public static function remove(string $name) {
        return F::remove(self::file($name));
    }
}
