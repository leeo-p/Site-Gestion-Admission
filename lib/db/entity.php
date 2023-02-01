<?php
namespace DB;

define(__NAMESPACE__ . "\DATA_DIR", realpath(__DIR__ . "/../../data"));

abstract class Entity {
    public $id = null;
    protected $data = [];

    public function __construct($id) {
        $dir = self::dirname($id);
        if (!is_dir($dir) or $id === "")
            throw new ExistenceException(static::class . " $id does not exist");
        $this->id = $id;
    }

    public static function create($id) {
        $dir = self::dirname($id);
        if (!is_dir($dir))
            if (!mkdir($dir))
                throw new \RuntimeException("cannot create directory $dir ($id)");
        return new static($id);
    }

    public function remove() {
        $dir = self::dirname($this->id);
        $dirit = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($dirit, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir())
                rmdir($file->getRealPath());
            else
                unlink($file->getRealPath());
        }
        rmdir($dir);
    }

    public static function getAll(): array {
        $dir = DATA_DIR . "/" . static::DIRNAME . "/";
        $scanned = scandir($dir);
        if ($scanned === false)
            throw new \RuntimeException("cannot scan directory $dir");
        return array_map(function ($v) {
            return new static(base64url_decode($v));
        }, array_filter($scanned, function ($v) {
            return preg_match("/^[0-9A-Za-z_-]+={0,2}$/", $v);
        }));
    }

    private static function dirname(string $id): string {
        return DATA_DIR . "/" . static::DIRNAME . "/" . base64url_encode($id);
    }

    private function filename(string $name): string {
        return self::dirname($this->id) . "/" . $name;
    }

    public static function toStored(array $spec, $value): string {
        $type = $spec[0];
        if (is_subclass_of($type, self::class)) {
            $e = is_a($value, $type) ? $value : new $type($value);
            return strval($e->id);
        }

        $type = "DB\\Storers\\$type";
        return $type::toStored($spec, $value);
    }

    public static function fromStored(array $spec, string $value) {
        $type = $spec[0];
        if (is_subclass_of($type, self::class))
            return new $type($value);

        $type = "DB\\Storers\\$type";
        return $type::fromStored($spec, $value);
    }

    public function __set(string $name, $value) {
        if ($name === "id")
            throw new \InvalidArgumentException("cannot set id");
        if (!isset(static::TABLE[$name]))
            throw new \InvalidArgumentException("$name field does not exist in " . static::class);

        $to_be_stored = self::toStored(static::TABLE[$name], $value);
        file_put_contents($this->filename($name), $to_be_stored);
        $data[$name] = $to_be_stored;
    }

    public function __get(string $name) {
        if ($name === "id")
            return $this->id;
        if (!isset(static::TABLE[$name]))
            throw new \InvalidArgumentException("$name field does not exist in " . static::class);
        if (!isset($this->data[$name])) {
            $file = $this->filename($name);
            if (!file_exists($file))
                return null;

            $this->data[$name] = trim(file_get_contents($file));
        }

        return self::fromStored(static::TABLE[$name], $this->data[$name]);
    }

    public function __isset(string $name): bool {
        return isset($this->data[$name]) or file_exists($this->filename($name));
    }

    public function __unset(string $name) {
        if (!isset(static::TABLE[$name]))
            throw new \InvalidArgumentException("$name field does not exist in " . static::class);
        unlink($this->filename($name));
        unset($this->data[$name]);
    }
}
?>
