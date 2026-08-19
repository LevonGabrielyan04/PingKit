<?php

namespace App\Enums;

enum HttpMethod: int
{
    case Get = 1;
    case Head = 2;
    case Post = 3;
    case Put = 4;
    case Delete = 5;
    case Connect = 6;
    case Options = 7;
    case Trace = 8;
    case Patch = 9;

    public function label(): string
    {
        return match ($this) {
            self::Get => 'GET',
            self::Head => 'HEAD',
            self::Post => 'POST',
            self::Put => 'PUT',
            self::Delete => 'DELETE',
            self::Connect => 'CONNECT',
            self::Options => 'OPTIONS',
            self::Trace => 'TRACE',
            self::Patch => 'PATCH',
        };
    }
}
