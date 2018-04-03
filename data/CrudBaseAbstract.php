<?php


abstract class CrudBaseAbstract
{
    protected $connection;

    protected $table;

    protected $attributes = [];

    protected $fillable = [];

    final public function __construct()
    {
        $config = require('../config.php');

        try {
            $this->connection = new PDO(
                $config['database']['dsn'],
                $config['database']['username'],
                $config['database']['password']
            );
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar com o banco de dados", 1);
        }
    }

    public function all()
    {
        $conn = $this->connection;

        $sql = sprintf('SELECT * FROM %s', $this->table);
        $result = $conn->query($sql);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($attributes)
    {
        if (isset($this->attributes['id'])) {
            return $this->attributes;
        }

        $this->fill($attributes);

        $conn = $this->connection;
        $stmt = $conn->prepare($this->prepareCreateQuery());
        $stmt->execute(array_values($this->attributes));

        $this->attributes['id'] = $conn->lastInsertId();

        return $this->attributes;
    }

    protected function prepareCreateQuery()
    {
        $fields = array_keys($this->attributes);
        $params = array_map(function($el) { return "?"; }, $fields);

        $query = sprintf(
            'INSERT INTO %s(%s) VALUES(%s);',
            $this->table,
            implode(', ', $fields),
            implode(', ', $params)
        );

        return $query;
    }

    public function read($id)
    {
        $conn = $this->connection;

        $sql = sprintf('SELECT * FROM %s WHERE id = ?', $this->table);
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($id));

        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->attributes = $data;
        }
        else {
            throw new Exception("Registro não encontrado", 1);
        }

        return $this->attributes;
    }

    public function update($attributes)
    {
        if (!isset($this->attributes['id'])) {
            throw new Exception("Erro: ID não esta presente.", 1);
        }

        $this->fill($attributes);

        $conn = $this->connection;
        $stmt = $conn->prepare($this->prepareUpdateQuery());
        $stmt->execute($this->prepareUpdateValues());

        return $this->attributes;
    }

    protected function prepareUpdateQuery()
    {
        $fields = array_keys($this->attributes);
        $fields = array_filter($fields, function($el) { return $el !== 'id'; });
        $params = array_map(function($el) { return "$el = :$el"; }, $fields);

        $query = sprintf(
            'UPDATE %s SET %s WHERE id = :id;',
            $this->table,
            implode(', ', $params)
        );

        return $query;
    }

    protected function prepareUpdateValues()
    {
        $values = [];
        foreach ($this->attributes as $key => $value) {
            $values[":$key"] = $value;
        }

        return $values;
    }

    public function fill($attributes)
    {
        if (!is_array($attributes)) {
            throw new \Exception("Attributes not a array.");
        }

        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return;
    }
}
