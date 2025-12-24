<?php

namespace Core\Logs;

use Config\nucleo\nucleologs\nucleo;

class Logs extends nucleo
{
    /** ℹ️ Info */
    public static function info(string $msg): void
    {
        static::salvarJson("Info", static::montarDados($msg, "Info"));
    }

    /** ✅ Success */
    public static function success(string $msg): void
    {
        static::salvarJson("Sucess", static::montarDados($msg, "Success"));
    }

    /** 📝 SQL */
    public static function sql(string $query): void
    {
        static::salvarJson("Sql", static::montarDados($query, "Sql"));
    }

    /** ❌ Error */
    public static function error(string $msg): void
    {
        static::salvarJson("Error", static::montarDados($msg, "Error"));
    }
    public static function warning(string $msg): void
    {
        static::salvarJson("warning", static::montarDados($msg, "warning"));
    }
}
