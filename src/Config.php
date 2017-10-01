<?php
namespace app;

class Config
{
    public static function load($configName)
    {
        $filePath = __DIR__ . "/config/" . $configName . ".yaml";
        return yaml_parse_file($filePath);
    }
}