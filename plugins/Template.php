<?php

class Template
{
    protected $templatePath;
    protected $templateFile;

    protected $prefix = '/{%';
    protected $suffix = '%}/';
    protected $fields;

    public function __construct($templateFile)
    {
        $this->templatePath = __DIR__ .'/../templates/';

        $this->templateFile = $templateFile;
    }

    public function __set($key, $value)
    {
        $this->parser($key, $value);
    }

    public function clearValues()
    {
        $this->fields = [];

        return $this;
    }

    public function parser($key, $value)
    {
        $key = trim($key);

        if (!$key) {
            return $this;
        }

        $this->fields['keys'][] = $this->prefix . $key . $this->suffix;
        $this->fields['values'][] = trim($value);

        return $this;
    }

    public function render()
    {
        $rawFile = @file_get_contents($this->templatePath . $this->templateFile, 1);

        $renderedFile = '';
        if (isset($this->fields['keys'])) {
            $renderedFile = preg_replace(
                $this->fields['keys'],
                $this->fields['values'],
                $rawFile
            );
        }

        return $renderedFile ?: $rawFile;
    }
}