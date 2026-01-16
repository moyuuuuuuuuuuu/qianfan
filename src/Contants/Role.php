<?php

namespace Moyuuuuuuuu\QianFan\Contants;

enum Role: string
{
    case SYSTEM    = 'system';
    case USER      = 'user';
    case ASSISTANT = 'assistant';
    case TOOL      = 'tool';
}
