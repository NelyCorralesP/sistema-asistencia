<?php
/**
 * Generador QR SVG autonomo (Version 2, Nivel L - hasta 32 bytes)
 * No requiere internet ni librerias externas.
 */
class QrSvg
{
    public static function svg($texto, $tamano = 160)
    {
        $matriz = self::matriz($texto);
        $n = count($matriz);
        $margen = 3;
        $total = $n + $margen * 2;
        $d = '';
        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($matriz[$y][$x] === 1) {
                    $px = $x + $margen;
                    $py = $y + $margen;
                    $d .= "M{$px},{$py}h1v1h-1z";
                }
            }
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . $total . '" width="' . (int)$tamano . '" height="' . (int)$tamano . '" shape-rendering="crispEdges"><rect width="100%" height="100%" fill="#ffffff"/><path d="' . $d . '" fill="#092c40"/></svg>';
    }

    private static function matriz($texto)
    {
        // Version 2-L: 25x25, 34 codewords datos, 10 codewords ECC (total 44)
        $bytes = array_values(unpack('C*', substr((string)$texto, 0, 32)));
        $len = count($bytes);

        // Construir flujo de bits: Modo Byte (0100) + longitud (8 bits) + datos + terminador
        $bits = '0100' . str_pad(decbin($len), 8, '0', STR_PAD_LEFT);
        foreach ($bytes as $b) {
            $bits .= str_pad(decbin($b), 8, '0', STR_PAD_LEFT);
        }
        $bits .= str_repeat('0', min(4, 34 * 8 - strlen($bits)));
        while (strlen($bits) % 8 !== 0) {
            $bits .= '0';
        }
        $pads = [0xEC, 0x11];
        $pi = 0;
        while (strlen($bits) < 34 * 8) {
            $bits .= str_pad(decbin($pads[$pi % 2]), 8, '0', STR_PAD_LEFT);
            $pi++;
        }

        $data = [];
        for ($i = 0; $i < 34; $i++) {
            $data[] = bindec(substr($bits, $i * 8, 8));
        }

        $ecc = self::reedSolomon($data, 10);
        $codewords = array_merge($data, $ecc);

        $allBits = '';
        foreach ($codewords as $cw) {
            $allBits .= str_pad(decbin($cw), 8, '0', STR_PAD_LEFT);
        }
        $allBits .= '0000000'; // 7 remainder bits for V2

        $n = 25;
        $m = array_fill(0, $n, array_fill(0, $n, null));
        $func = array_fill(0, $n, array_fill(0, $n, false));

        // Patrones de busqueda (Finder)
        self::ponerFinder($m, $func, 0, 0);
        self::ponerFinder($m, $func, $n - 7, 0);
        self::ponerFinder($m, $func, 0, $n - 7);

        // Patron de alineacion V2 en (18, 18)
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $v = (max(abs($dx), abs($dy)) !== 1) ? 1 : 0;
                $m[18 + $dy][18 + $dx] = $v;
                $func[18 + $dy][18 + $dx] = true;
            }
        }

        // Patrones de sincronizacion (Timing)
        for ($i = 8; $i < $n - 8; $i++) {
            $v = ($i % 2 === 0) ? 1 : 0;
            if (!$func[6][$i]) { $m[6][$i] = $v; $func[6][$i] = true; }
            if (!$func[$i][6]) { $m[$i][6] = $v; $func[$i][6] = true; }
        }

        // Modulo oscuro fijo y reserva de formato
        $m[$n - 8][8] = 1;
        $func[$n - 8][8] = true;
        for ($i = 0; $i < 9; $i++) {
            if ($i !== 6) {
                $func[8][$i] = true;
                $func[$i][8] = true;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $func[8][$n - 1 - $i] = true;
            $func[$n - 1 - $i][8] = true;
        }

        // Colocar bits en zigzag con mascara 0: (y + x) % 2 == 0
        $bi = 0;
        $up = true;
        for ($col = $n - 1; $col > 0; $col -= 2) {
            if ($col === 6) $col--;
            for ($step = 0; $step < $n; $step++) {
                $y = $up ? ($n - 1 - $step) : $step;
                for ($c = 0; $c < 2; $c++) {
                    $x = $col - $c;
                    if (!$func[$y][$x]) {
                        $bit = ($bi < strlen($allBits)) ? (int)$allBits[$bi++] : 0;
                        if (($y + $x) % 2 === 0) {
                            $bit ^= 1;
                        }
                        $m[$y][$x] = $bit;
                    }
                }
            }
            $up = !$up;
        }

        // Bits de formato para Nivel L (01) + Mascara 0 (000) => 0x77C4 (111011111000100)
        $fmt = '111011111000100';
        $pos1 = [[8,0],[8,1],[8,2],[8,3],[8,4],[8,5],[8,7],[8,8],[7,8],[5,8],[4,8],[3,8],[2,8],[1,8],[0,8]];
        $pos2 = [[$n-1,8],[$n-2,8],[$n-3,8],[$n-4,8],[$n-5,8],[$n-6,8],[$n-7,8],[8,$n-8],[8,$n-7],[8,$n-6],[8,$n-5],[8,$n-4],[8,$n-3],[8,$n-2],[8,$n-1]];
        for ($i = 0; $i < 15; $i++) {
            $v = (int)$fmt[$i];
            $m[$pos1[$i][0]][$pos1[$i][1]] = $v;
            $m[$pos2[$i][0]][$pos2[$i][1]] = $v;
        }

        return $m;
    }

    private static function ponerFinder(&$m, &$func, $ox, $oy)
    {
        for ($y = -1; $y <= 7; $y++) {
            for ($x = -1; $x <= 7; $x++) {
                $py = $oy + $y;
                $px = $ox + $x;
                if ($py < 0 || $py >= 25 || $px < 0 || $px >= 25) continue;
                $in7 = ($x >= 0 && $x <= 6 && $y >= 0 && $y <= 6);
                $v = 0;
                if ($in7) {
                    $borde = ($x === 0 || $x === 6 || $y === 0 || $y === 6);
                    $centro = ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4);
                    $v = ($borde || $centro) ? 1 : 0;
                }
                $m[$py][$px] = $v;
                $func[$py][$px] = true;
            }
        }
    }

    private static function reedSolomon($data, $ecCount)
    {
        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) $x ^= 0x11D;
        }
        for ($i = 255; $i < 512; $i++) {
            $exp[$i] = $exp[$i - 255];
        }

        $gen = [1];
        for ($i = 0; $i < $ecCount; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            for ($j = 0; $j < count($gen); $j++) {
                $next[$j] ^= $gen[$j];
                $next[$j + 1] ^= $exp[$log[$gen[$j]] + $i];
            }
            $gen = $next;
        }

        $res = array_fill(0, $ecCount, 0);
        foreach ($data as $b) {
            $factor = $b ^ array_shift($res);
            $res[] = 0;
            if ($factor !== 0) {
                for ($j = 0; $j < $ecCount; $j++) {
                    $res[$j] ^= $exp[$log[$gen[$j + 1]] + $log[$factor]];
                }
            }
        }
        return $res;
    }
}
