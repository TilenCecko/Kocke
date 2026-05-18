Add-Type -AssemblyName System.Drawing

$root = Split-Path -Parent $PSScriptRoot
$imgDir = Join-Path $root "img"
$framesDir = Join-Path $imgDir "dice-anim-frames"

if (-not (Test-Path -LiteralPath $framesDir)) {
    New-Item -ItemType Directory -Path $framesDir | Out-Null
}

function New-Canvas($size) {
    $bmp = New-Object System.Drawing.Bitmap($size, $size, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $graphics = [System.Drawing.Graphics]::FromImage($bmp)
    $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $graphics.Clear([System.Drawing.Color]::Transparent)
    return @{ Bitmap = $bmp; Graphics = $graphics }
}

function Add-RoundedRect($graphics, $x, $y, $w, $h, $r) {
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath
    $d = $r * 2
    $path.AddArc($x, $y, $d, $d, 180, 90)
    $path.AddArc($x + $w - $d, $y, $d, $d, 270, 90)
    $path.AddArc($x + $w - $d, $y + $h - $d, $d, $d, 0, 90)
    $path.AddArc($x, $y + $h - $d, $d, $d, 90, 90)
    $path.CloseFigure()
    return $path
}

function Get-PipPositions($value, $x, $y, $size) {
    $left = $x + $size * 0.27
    $mid = $x + $size * 0.50
    $right = $x + $size * 0.73
    $top = $y + $size * 0.27
    $center = $y + $size * 0.50
    $bottom = $y + $size * 0.73

    function Pip($px, $py) {
        [PSCustomObject]@{ X = [float]$px; Y = [float]$py }
    }

    switch ($value) {
        1 { return @(Pip $mid $center) }
        2 { return @(Pip $left $top; Pip $right $bottom) }
        3 { return @(Pip $left $top; Pip $mid $center; Pip $right $bottom) }
        4 { return @(Pip $left $top; Pip $right $top; Pip $left $bottom; Pip $right $bottom) }
        5 { return @(Pip $left $top; Pip $right $top; Pip $mid $center; Pip $left $bottom; Pip $right $bottom) }
        6 { return @(Pip $left $top; Pip $right $top; Pip $left $center; Pip $right $center; Pip $left $bottom; Pip $right $bottom) }
    }
}

function Draw-Die($graphics, $value, $x, $y, $size, $angle) {
    $state = $graphics.Save()
    $graphics.TranslateTransform($x + $size / 2, $y + $size / 2)
    $graphics.RotateTransform($angle)
    $graphics.TranslateTransform(-($x + $size / 2), -($y + $size / 2))

    $shadow = Add-RoundedRect $graphics ($x + 6) ($y + 9) $size $size 28
    $shadowBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(72, 0, 0, 0))
    $graphics.FillPath($shadowBrush, $shadow)

    $body = Add-RoundedRect $graphics $x $y $size $size 28
    $gradient = New-Object System.Drawing.Drawing2D.LinearGradientBrush(
        (New-Object System.Drawing.RectangleF($x, $y, $size, $size)),
        [System.Drawing.Color]::FromArgb(255, 255, 255, 255),
        [System.Drawing.Color]::FromArgb(255, 222, 226, 238),
        135
    )
    $graphics.FillPath($gradient, $body)

    $borderPen = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(255, 178, 184, 201), 3)
    $graphics.DrawPath($borderPen, $body)

    $shine = Add-RoundedRect $graphics ($x + 14) ($y + 12) ($size - 40) 28 14
    $shineBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(95, 255, 255, 255))
    $graphics.FillPath($shineBrush, $shine)

    $pipBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(255, 30, 34, 45))
    $pipHighlight = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(90, 255, 255, 255))
    $pipSize = $size * 0.15

    foreach ($pos in (Get-PipPositions $value $x $y $size)) {
        $px = $pos.X - $pipSize / 2
        $py = $pos.Y - $pipSize / 2
        $graphics.FillEllipse($pipBrush, $px, $py, $pipSize, $pipSize)
        $graphics.FillEllipse($pipHighlight, $px + $pipSize * 0.18, $py + $pipSize * 0.16, $pipSize * 0.32, $pipSize * 0.24)
    }

    $graphics.Restore($state)
}

for ($value = 1; $value -le 6; $value++) {
    $canvas = New-Canvas 180
    Draw-Die $canvas.Graphics $value 18 18 136 0
    $out = Join-Path $imgDir "dice$value.png"
    $canvas.Bitmap.Save($out, [System.Drawing.Imaging.ImageFormat]::Png)
    $canvas.Graphics.Dispose()
    $canvas.Bitmap.Dispose()
}

for ($frame = 0; $frame -lt 24; $frame++) {
    $canvas = New-Canvas 180
    $value = ($frame % 6) + 1
    $angle = [Math]::Sin($frame / 24 * [Math]::PI * 2) * 18
    $bounce = [Math]::Sin($frame / 24 * [Math]::PI * 4) * 8
    Draw-Die $canvas.Graphics $value 18 (18 + $bounce) 136 $angle
    $framePath = Join-Path $framesDir ("frame-{0:D3}.png" -f $frame)
    $canvas.Bitmap.Save($framePath, [System.Drawing.Imaging.ImageFormat]::Png)
    $canvas.Graphics.Dispose()
    $canvas.Bitmap.Dispose()
}

ffmpeg -y -framerate 16 -i (Join-Path $framesDir "frame-%03d.png") -vf "split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse" (Join-Path $imgDir "dice-anim.gif") | Out-Null

$resolvedFrames = Resolve-Path -LiteralPath $framesDir
$resolvedImg = Resolve-Path -LiteralPath $imgDir
if ($resolvedFrames.Path.StartsWith($resolvedImg.Path)) {
    Remove-Item -LiteralPath $framesDir -Recurse -Force
}
