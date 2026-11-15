from __future__ import annotations

import base64
import html
import json
import subprocess
import textwrap
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
SVG_PATH = BASE_DIR / "gambar_3_1_tahapan_penelitian.svg"
PNG_PATH = BASE_DIR / "gambar_3_1_tahapan_penelitian.png"

WIDTH = 1800
HEIGHT = 4050
LEFT_X = 80
CENTER_X = 690
RIGHT_X = 1180
BOX_W = 520
OVAL_W = 420
OVAL_H = 112
ROW_Y = [185, 680, 1195, 1720, 2245, 2760, 3280]
FONT = "Times New Roman, Times, serif"


STAGES = [
    {
        "process": "Analisis Sistem",
        "input": ["Sistem Penjualan KeFrec Coffee Shop yang Sedang Berjalan"],
        "output": [
            "Proses Bisnis Berjalan",
            "Identifikasi Permasalahan",
            "Analisis Alur Transaksi",
        ],
    },
    {
        "process": "Analisis Kebutuhan",
        "input": [
            "Hasil Observasi",
            "Hasil Wawancara",
            "Studi Literatur",
            "Hasil Analisis Sistem",
        ],
        "output": [
            "Kebutuhan Fungsional: menu produk, cart, checkout, pembayaran tunai dan Midtrans, approval kasir, dapur, stok, pengguna, transaksi, komplain, laporan",
            "Kebutuhan Non-Fungsional: keamanan akses, kemudahan penggunaan, performa, akses melalui web browser",
        ],
    },
    {
        "process": "Perancangan Sistem",
        "input": ["Daftar Kebutuhan Sistem", "Workflow Bisnis", "Aturan Sistem"],
        "output": [
            "Konsep Arsitektur MVC",
            "Arsitektur Perangkat Lunak",
            "Diagram Konteks",
            "Data Flow Diagram",
            "Use Case Diagram",
            "Sequence Diagram",
            "Entity Relationship Diagram",
        ],
    },
    {
        "process": "Pembuatan Interface",
        "input": [
            "Hasil Perancangan Sistem",
            "Perancangan Menu",
            "Perancangan Input",
            "Perancangan Output",
        ],
        "output": [
            "Landing Page dan Menu Produk",
            "Keranjang dan Checkout",
            "Halaman Member",
            "Halaman Kasir",
            "Halaman Dapur",
            "Panel Admin dan Owner",
        ],
    },
    {
        "process": "Pembuatan Sistem",
        "input": [
            "PHP 8.2",
            "Laravel 12",
            "Filament",
            "MySQL",
            "Composer",
            "Apache/XAMPP",
            "Midtrans",
        ],
        "output": [
            "Sistem Informasi Penjualan Pada KeFrec Coffee Shop Berbasis Website"
        ],
    },
    {
        "process": "Implementasi Sistem",
        "input": [
            "Konfigurasi Aplikasi",
            "Konfigurasi Database",
            "Migrasi Database",
            "Integrasi Midtrans",
            "Pengisian Data Awal",
            "Black Box Testing",
            "Evaluasi Sistem",
        ],
        "output": [
            "Frontend dan Backend Terintegrasi",
            "Fungsi Sistem Berjalan Sesuai Kebutuhan",
            "Hasil Pengujian Black Box",
        ],
    },
    {
        "process": "Aplikasi/Sistem",
        "input": [
            "Data Kategori",
            "Data Produk",
            "Data Bahan Baku",
            "Data Pengguna",
            "Data Transaksi",
            "Data Pembayaran",
            "Data Komplain",
            "Data Riwayat Stok",
        ],
        "output": [
            "Informasi Produk",
            "Informasi Pesanan",
            "Informasi Transaksi",
            "Informasi Pembayaran",
            "Informasi Stok Bahan Baku",
            "Informasi Komplain",
            "Informasi Laporan Penjualan",
        ],
    },
]


def wrap_lines(text: str, width: int) -> list[str]:
    return textwrap.wrap(text, width=width, break_long_words=False) or [""]


def box_lines(lines: list[str], wrap_width: int) -> list[str]:
    rendered: list[str] = []
    for line in lines:
        rendered.extend(wrap_lines(line, wrap_width))
    return rendered


def rounded_box_height(line_count: int) -> int:
    return max(88, 38 + line_count * 31)


def arrow(x1: int, y1: int, x2: int, y2: int) -> str:
    return (
        f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" '
        'stroke="#000" stroke-width="3" marker-end="url(#arrow)" />'
    )


def svg_text(lines: list[str], x: int, y: int, size: int = 27, weight: str = "normal") -> str:
    tspans = []
    for index, line in enumerate(lines):
        dy = 0 if index == 0 else size + 5
        tspans.append(
            f'<tspan x="{x}" dy="{dy}">{html.escape(line)}</tspan>'
        )
    return (
        f'<text x="{x}" y="{y}" font-family="{FONT}" font-size="{size}" '
        f'font-weight="{weight}" text-anchor="middle" dominant-baseline="middle">'
        + "".join(tspans)
        + "</text>"
    )


def svg_box(x: int, y: int, w: int, lines: list[str]) -> tuple[str, int]:
    h = rounded_box_height(len(lines))
    text_y = y - ((len(lines) - 1) * 16)
    body = [
        f'<rect x="{x}" y="{y - h // 2}" width="{w}" height="{h}" rx="24" ry="24" '
        'fill="#fff" stroke="#000" stroke-width="3" />',
        svg_text(lines, x + w // 2, text_y, 27),
    ]
    return "\n".join(body), h


def make_svg() -> str:
    parts = [
        '<?xml version="1.0" encoding="UTF-8"?>',
        f'<svg xmlns="http://www.w3.org/2000/svg" width="{WIDTH}" height="{HEIGHT}" viewBox="0 0 {WIDTH} {HEIGHT}">',
        "<defs>",
        '<marker id="arrow" markerWidth="14" markerHeight="14" refX="12" refY="7" orient="auto" markerUnits="strokeWidth">',
        '<path d="M 0 0 L 14 7 L 0 14 z" fill="#000" />',
        "</marker>",
        "</defs>",
        '<rect x="0" y="0" width="100%" height="100%" fill="#fff" />',
    ]

    center_mid_x = CENTER_X + OVAL_W // 2

    for index, stage in enumerate(STAGES):
        y = ROW_Y[index]
        input_lines = box_lines(stage["input"], 34)
        output_lines = box_lines(stage["output"], 36)

        input_svg, input_h = svg_box(LEFT_X, y, BOX_W, input_lines)
        output_svg, output_h = svg_box(RIGHT_X, y, BOX_W, output_lines)
        parts.extend([input_svg, output_svg])

        parts.append(
            f'<ellipse cx="{center_mid_x}" cy="{y}" rx="{OVAL_W // 2}" ry="{OVAL_H // 2}" '
            'fill="#fff" stroke="#000" stroke-width="3" />'
        )
        parts.append(svg_text(wrap_lines(stage["process"], 22), center_mid_x, y, 30, "bold"))

        parts.append(arrow(LEFT_X + BOX_W, y, CENTER_X, y))
        parts.append(arrow(CENTER_X + OVAL_W, y, RIGHT_X, y))

        if index < len(STAGES) - 1:
            next_y = ROW_Y[index + 1]
            parts.append(arrow(center_mid_x, y + OVAL_H // 2, center_mid_x, next_y - OVAL_H // 2))

    parts.append(
        f'<text x="{WIDTH // 2}" y="{HEIGHT - 90}" font-family="{FONT}" font-size="36" '
        'font-weight="bold" text-anchor="middle">Gambar 3.1 Tahapan Penelitian</text>'
    )
    parts.append("</svg>")
    return "\n".join(parts)


def make_png_with_powershell() -> None:
    payload = {
        "width": WIDTH,
        "height": HEIGHT,
        "left_x": LEFT_X,
        "center_x": CENTER_X,
        "right_x": RIGHT_X,
        "box_w": BOX_W,
        "oval_w": OVAL_W,
        "oval_h": OVAL_H,
        "row_y": ROW_Y,
        "stages": STAGES,
        "png_path": str(PNG_PATH),
    }
    encoded = base64.b64encode(json.dumps(payload).encode("utf-8")).decode("ascii")
    ps_script = rf"""
$ErrorActionPreference = 'Stop'
$json = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String('{encoded}'))
$data = $json | ConvertFrom-Json
Add-Type -AssemblyName System.Drawing
$bmp = New-Object Drawing.Bitmap ([int]$data.width), ([int]$data.height)
$bmp.SetResolution(300, 300)
$g = [Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [Drawing.Text.TextRenderingHint]::AntiAliasGridFit
$g.Clear([Drawing.Color]::White)
$pen = New-Object Drawing.Pen ([Drawing.Color]::Black), 3
$brush = [Drawing.Brushes]::Black
$font = New-Object Drawing.Font ('Times New Roman', 27, [Drawing.FontStyle]::Regular, [Drawing.GraphicsUnit]::Pixel)
$fontBold = New-Object Drawing.Font ('Times New Roman', 30, [Drawing.FontStyle]::Bold, [Drawing.GraphicsUnit]::Pixel)
$captionFont = New-Object Drawing.Font ('Times New Roman', 36, [Drawing.FontStyle]::Bold, [Drawing.GraphicsUnit]::Pixel)
$sfCenter = New-Object Drawing.StringFormat
$sfCenter.Alignment = [Drawing.StringAlignment]::Center
$sfCenter.LineAlignment = [Drawing.StringAlignment]::Center

function Wrap-Lines([string]$text, [int]$max) {{
    $words = $text -split ' '
    $lines = New-Object Collections.Generic.List[string]
    $line = ''
    foreach ($word in $words) {{
        if ($line.Length -eq 0) {{
            $line = $word
        }} elseif (($line.Length + 1 + $word.Length) -le $max) {{
            $line = "$line $word"
        }} else {{
            $lines.Add($line)
            $line = $word
        }}
    }}
    if ($line.Length -gt 0) {{ $lines.Add($line) }}
    return $lines
}}

function Flatten-Lines($items, [int]$max) {{
    $lines = New-Object Collections.Generic.List[string]
    foreach ($item in $items) {{
        foreach ($line in (Wrap-Lines ([string]$item) $max)) {{ $lines.Add($line) }}
    }}
    return $lines
}}

function Rounded-Rect([int]$x, [int]$y, [int]$w, [int]$h, [int]$r) {{
    $path = New-Object Drawing.Drawing2D.GraphicsPath
    $d = $r * 2
    $path.AddArc($x, $y, $d, $d, 180, 90)
    $path.AddArc($x + $w - $d, $y, $d, $d, 270, 90)
    $path.AddArc($x + $w - $d, $y + $h - $d, $d, $d, 0, 90)
    $path.AddArc($x, $y + $h - $d, $d, $d, 90, 90)
    $path.CloseFigure()
    return $path
}}

function Draw-Arrow([int]$x1, [int]$y1, [int]$x2, [int]$y2) {{
    $g.DrawLine($pen, $x1, $y1, $x2, $y2)
    $angle = [Math]::Atan2($y2 - $y1, $x2 - $x1)
    $len = 18
    $a1 = $angle + [Math]::PI * 0.82
    $a2 = $angle - [Math]::PI * 0.82
    $p0 = New-Object Drawing.PointF -ArgumentList ([single]$x2), ([single]$y2)
    $p1 = New-Object Drawing.PointF -ArgumentList ([single]($x2 + $len * [Math]::Cos($a1))), ([single]($y2 + $len * [Math]::Sin($a1)))
    $p2 = New-Object Drawing.PointF -ArgumentList ([single]($x2 + $len * [Math]::Cos($a2))), ([single]($y2 + $len * [Math]::Sin($a2)))
    [Drawing.PointF[]]$points = @($p0, $p1, $p2)
    $g.FillPolygon($brush, $points)
}}

function Draw-TextLines($lines, [int]$x, [int]$y, [int]$w, [int]$h, $fontToUse) {{
    if ($lines -is [string]) {{
        $lines = @($lines)
    }}
    $lineHeight = 32
    $total = $lines.Count * $lineHeight
    $startY = $y + (($h - $total) / 2)
    for ($i = 0; $i -lt $lines.Count; $i++) {{
        $rect = New-Object Drawing.RectangleF($x, ($startY + $i * $lineHeight), $w, $lineHeight)
        $g.DrawString([string]$lines[$i], $fontToUse, $brush, $rect, $sfCenter)
    }}
}}

$centerMidX = [int]$data.center_x + [int]$data.oval_w / 2
for ($i = 0; $i -lt $data.stages.Count; $i++) {{
    $stage = $data.stages[$i]
    $y = [int]$data.row_y[$i]
    $inputLines = Flatten-Lines $stage.input 34
    $outputLines = Flatten-Lines $stage.output 36
    $inputH = [Math]::Max(88, 38 + $inputLines.Count * 31)
    $outputH = [Math]::Max(88, 38 + $outputLines.Count * 31)

    $inputPath = Rounded-Rect ([int]$data.left_x) ([int]($y - $inputH / 2)) ([int]$data.box_w) ([int]$inputH) 24
    $outputPath = Rounded-Rect ([int]$data.right_x) ([int]($y - $outputH / 2)) ([int]$data.box_w) ([int]$outputH) 24
    $g.DrawPath($pen, $inputPath)
    $g.DrawPath($pen, $outputPath)
    Draw-TextLines $inputLines ([int]$data.left_x) ([int]($y - $inputH / 2)) ([int]$data.box_w) ([int]$inputH) $font
    Draw-TextLines $outputLines ([int]$data.right_x) ([int]($y - $outputH / 2)) ([int]$data.box_w) ([int]$outputH) $font

    $g.DrawEllipse($pen, ([int]$data.center_x), ([int]($y - [int]$data.oval_h / 2)), ([int]$data.oval_w), ([int]$data.oval_h))
    $processLines = @(Wrap-Lines ([string]$stage.process) 22)
    Draw-TextLines $processLines ([int]$data.center_x) ([int]($y - [int]$data.oval_h / 2)) ([int]$data.oval_w) ([int]$data.oval_h) $fontBold

    Draw-Arrow ([int]$data.left_x + [int]$data.box_w) $y ([int]$data.center_x) $y
    Draw-Arrow ([int]$data.center_x + [int]$data.oval_w) $y ([int]$data.right_x) $y
    if ($i -lt ($data.stages.Count - 1)) {{
        Draw-Arrow $centerMidX ([int]($y + [int]$data.oval_h / 2)) $centerMidX ([int]([int]$data.row_y[$i + 1] - [int]$data.oval_h / 2))
    }}
}}

$captionRect = New-Object Drawing.RectangleF(0, ([int]$data.height - 125), ([int]$data.width), 60)
$g.DrawString('Gambar 3.1 Tahapan Penelitian', $captionFont, $brush, $captionRect, $sfCenter)
$bmp.Save([string]$data.png_path, [Drawing.Imaging.ImageFormat]::Png)
$g.Dispose()
$bmp.Dispose()
"""
    subprocess.run(
        ["powershell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command", ps_script],
        check=True,
    )


def main() -> None:
    BASE_DIR.mkdir(parents=True, exist_ok=True)
    SVG_PATH.write_text(make_svg(), encoding="utf-8", newline="\n")
    make_png_with_powershell()
    print(f"Created {SVG_PATH}")
    print(f"Created {PNG_PATH}")


if __name__ == "__main__":
    main()
