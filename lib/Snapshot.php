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

    public static function list()
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

    public static function read(string $name)
    {
        if ($content = F::read(self::file($name))) {
            return json_decode($content, true);
        } else {
            throw new Exception('Could not read snapshot', 500);
        }
    }

    public static function remove(string $name) {
        return F::remove(self::file($name));
    }
}
