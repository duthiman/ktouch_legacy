<?php

if (!defined('OPENCLAW_X_API_KEY')) {
    define('OPENCLAW_X_API_KEY', 'a23c6a3c5e4b44458eda402a7e9bdadf26fab8d888e44c87935590c23cd4f662');
}

function openclaw_api_key(): string
{
    return OPENCLAW_X_API_KEY;
}
