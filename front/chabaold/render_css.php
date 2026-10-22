<?php

if (!function_exists('kp_render_print_template_id')) {
    function kp_render_print_template_id()
    {
        $template = function_exists('get_print_template') ? (int)get_print_template() : 1;
        return $template === 2 ? 2 : 1;
    }
}

if (!function_exists('kp_render_print_template_css')) {
    function kp_render_print_template_css($template = null)
    {
        $template = $template === null ? kp_render_print_template_id() : ((int)$template === 2 ? 2 : 1);
        $cssPath = realpath(__DIR__ . '/../assets/css/thermal_template' . $template . '.css');
        if (!$cssPath || !is_file($cssPath)) {
            return '';
        }

        $css = @file_get_contents($cssPath);
        return $css === false ? '' : $css;
    }
}
