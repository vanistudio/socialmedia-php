<?php
function hex_to_oklch($hex, $precision = 4) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) {
        throw new InvalidArgumentException("Hex color must be 6 characters long");
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r_linear = rgb_to_linear($r / 255.0);
    $g_linear = rgb_to_linear($g / 255.0);
    $b_linear = rgb_to_linear($b / 255.0);
    $xyz = linear_rgb_to_xyz($r_linear, $g_linear, $b_linear);
    $oklab = xyz_to_oklab($xyz[0], $xyz[1], $xyz[2]);
    $oklch = oklab_to_oklch($oklab[0], $oklab[1], $oklab[2]);
    return sprintf(
        "oklch(%s %s %s)",
        number_format($oklch[0], $precision, '.', ''),
        number_format($oklch[1], $precision, '.', ''),
        number_format($oklch[2], $precision, '.', '')
    );
}
function rgb_to_linear($value) {
    if ($value <= 0.04045) {
        return $value / 12.92;
    }
    return pow(($value + 0.055) / 1.055, 2.4);
}
function linear_rgb_to_xyz($r, $g, $b) {
    $x = 0.4124564 * $r + 0.3575761 * $g + 0.1804375 * $b;
    $y = 0.2126729 * $r + 0.7151522 * $g + 0.0721750 * $b;
    $z = 0.0193339 * $r + 0.1191920 * $g + 0.9503041 * $b;
    
    return [$x, $y, $z];
}
function xyz_to_oklab($x, $y, $z) {
    $l = 0.8189330101 * $x + 0.3618667424 * $y + -0.1288597137 * $z;
    $m = 0.0329845436 * $x + 0.9293118715 * $y + 0.0361456387 * $z;
    $s = 0.0482003018 * $x + 0.2643662691 * $y + 0.6338517070 * $z;
    $l_cbrt = $l >= 0 ? pow($l, 1/3) : -pow(-$l, 1/3);
    $m_cbrt = $m >= 0 ? pow($m, 1/3) : -pow(-$m, 1/3);
    $s_cbrt = $s >= 0 ? pow($s, 1/3) : -pow(-$s, 1/3);
    $L = 0.2104542553 * $l_cbrt + 0.7936177850 * $m_cbrt - 0.0040720468 * $s_cbrt;
    $a = 1.9779984951 * $l_cbrt - 2.4285922050 * $m_cbrt + 0.4505937099 * $s_cbrt;
    $b = 0.0259040371 * $l_cbrt + 0.7827717662 * $m_cbrt - 0.8086757660 * $s_cbrt;
    return [$L, $a, $b];
}
function oklab_to_oklch($L, $a, $b) {
    $l = $L;
    $c = sqrt($a * $a + $b * $b);
    $h = 0;
    if ($c > 0.0001) {
        $h_rad = atan2($b, $a);
        $h = rad2deg($h_rad);
        if ($h < 0) {
            $h += 360;
        }
    }
    
    return [$l, $c, $h];
}
function get_vanixjnk_oklch($hex) {
    return hex_to_oklch($hex);
}