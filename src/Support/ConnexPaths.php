<?php

namespace Torgodly\Connex\Support;

class ConnexPaths
{
    public static function javascript(string $filename): string
    {
        $published = resource_path('js/vendor/'.$filename);

        if (file_exists($published)) {
            return $published;
        }

        return dirname(__DIR__, 2).'/resources/js/'.$filename;
    }
}
