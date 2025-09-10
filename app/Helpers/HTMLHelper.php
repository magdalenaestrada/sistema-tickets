<?php

namespace App\Helpers;

use App\Enums\Models\ModelStatusEnum;

class HTMLHelper
{
    public static function link(string $url, string $mask = "Ir al enlace", string $target = "_blank"): string
    {
        return "<a href='{$url}' target='{$target}'>{$mask}</a>";
    }
    public static function generarButton(array $props): string
    {
        $title = $props["title"] ?? "";
        $element = $props["element"] ?? "button";
        $href = $props["href"] ?? "";
        $id = $props["id"] ?? "";
        $class = $props["class"] ?? "primary";
        $text = $props["text"] ?? "Boton";
        $type = $props["type"] ?? "button";
        $target = $props["target"] ?? "";
        $data = $props["data"] ?? [];
        $str_data = "";
        foreach ($data as $key => $value) {
            $str_data .= "data-{$key}='{$value}' ";
        }
        return "<{$element} href='{$href}' id='{$id}' type='{$type}' target='{$target}' data-container='body' data-bs-placement='top' data-bs-original-title='{$title}' data-bs-toggle='tooltip' {$str_data} class='btn {$class}'>{$text}</{$element}>";
    }
    public static function badge($enum, string $color = "primary")
    {
        return "<span class='badge badge-light rounded-pill text-bg-{$color}'>{$enum->name}</span>";
    }
    public static function badgeText($text, string $color = "primary")
    {
        return "<span class='badge badge-light rounded-pill text-bg-{$color}' style='min-width: 120px;'>{$text}</span>";
    }
    public static function image(string $src, string $alt = "", string $class = "", string $width = "", string $height = "")
    {
        return "<img src='{$src}' alt='{$alt}' class='{$class}' width='{$width}' height='{$height}'>";
    }
    public static function spanWithIcon(string $icon = "", string $text = "", array $data = [], string $class = "")
    {
        $str_data = "";
        foreach ($data as $key => $value) {
            $str_data .= "data-{$key}='{$value}' ";
        }
        return "<span class='link-icon {$class}' {$str_data}><i class='{$icon}'></i> {$text}</span>";
    }

    public static function linkWithIcon(string $icon = "", string $text = "", array $data = [], string $href = "", string $target = "_blank", string $class = "")
    {
        $str_data = "";
        foreach ($data as $key => $value) {
            $str_data .= "data-{$key}='{$value}' ";
        }
        return "<a href='{$href}' target='{$target}' class='link-icon {$class}' {$str_data}><i class='{$icon}'></i> {$text}</a>";
    }

    public static function imageWithLink(string $src, string $alt = "", string $class = "", string $width = "", string $height = "")
    {
        return "<a href='{$src}' target='_blank'><img src='{$src}' alt='{$alt}' class='{$class}' width='{$width}' height='{$height}'></a>";
    }

    public static function linkWithText(string $text, string $href = "", string $icon = "fa-solid fa-arrow-right", string $class = "", string $target = "_blank")
    {
        return "<a href='{$href}' target='{$target}' class='link-icon {$class}'><i class='{$icon}'></i> {$text}</a>";
    }
}
