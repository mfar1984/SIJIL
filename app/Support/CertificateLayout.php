<?php

namespace App\Support;

/**
 * Coordinate maths shared by the certificate designer and the PDF renderer.
 *
 * The designer and the renderer used to disagree on what x and y mean, which is
 * why text always needed nudging to look centred. The rules below are now the
 * single definition, and both sides follow them:
 *
 *   y  = the TOP edge of the text, in millimetres from the top of the page.
 *   x  = the anchor, in millimetres from the left of the page:
 *          left   -> x is the left edge of the text
 *          center -> x is the horizontal centre of the text
 *          right  -> x is the right edge of the text
 *
 *   fontSize is in POINTS, the same unit TCPDF uses.
 */
class CertificateLayout
{
    /** One PDF point in millimetres. */
    public const MM_PER_POINT = 25.4 / 72;

    /** TCPDF's K_CELL_HEIGHT_RATIO: line height as a multiple of font size. */
    public const LINE_HEIGHT_RATIO = 1.25;

    /**
     * Left and right cell padding TCPDF applies by default, in millimetres.
     *
     * TCPDF's constructor sets this to the left margin divided by ten, and the
     * default left margin is 15 mm. The renderer now zeroes it out, but the
     * value is still needed to undo what older templates were drawn with.
     */
    public const LEGACY_CELL_PADDING_MM = 1.5;

    /** Fixed cell height the old renderer used for every text element. */
    public const LEGACY_CELL_HEIGHT_MM = 10.0;

    /**
     * Height of one line of text, in millimetres.
     */
    public static function lineHeightMm(float $fontSizePt): float
    {
        return $fontSizePt * self::MM_PER_POINT * self::LINE_HEIGHT_RATIO;
    }

    /**
     * Turn an anchor into the left edge TCPDF needs to start drawing from.
     *
     * @param string $align one of left, center, right (or TCPDF's L, C, R)
     */
    public static function drawX(float $anchorX, float $textWidthMm, string $align): float
    {
        return match (self::normaliseAlign($align)) {
            'center' => $anchorX - ($textWidthMm / 2),
            'right' => $anchorX - $textWidthMm,
            default => $anchorX,
        };
    }

    /**
     * Normalise the many spellings of alignment used across the codebase.
     */
    public static function normaliseAlign(?string $align): string
    {
        return match (strtolower((string) $align)) {
            'c', 'center', 'centre' => 'center',
            'r', 'right' => 'right',
            default => 'left',
        };
    }

    /**
     * TCPDF alignment letter for an alignment name.
     */
    public static function tcpdfAlign(?string $align): string
    {
        return match (self::normaliseAlign($align)) {
            'center' => 'C',
            'right' => 'R',
            default => 'L',
        };
    }

    /**
     * Where the OLD renderer actually put the top of the text.
     *
     * It drew into a 10 mm tall cell whose top sat at y, with the text centred
     * vertically inside it, so the text floated 5 mm below y minus half a line.
     */
    public static function legacyTextTopMm(float $y, float $fontSizePt): float
    {
        return $y + (self::LEGACY_CELL_HEIGHT_MM / 2) - (self::lineHeightMm($fontSizePt) / 2);
    }

    /**
     * Where the OLD renderer actually anchored the text horizontally.
     *
     * Centred text ignored x completely and centred on the whole page, and
     * right aligned text was pinned to the right edge instead of to x.
     */
    public static function legacyAnchorXMm(float $x, string $align, float $pageWidthMm): float
    {
        return match (self::normaliseAlign($align)) {
            'center' => $pageWidthMm / 2,
            'right' => $pageWidthMm - self::LEGACY_CELL_PADDING_MM,
            default => $x + self::LEGACY_CELL_PADDING_MM,
        };
    }

    /**
     * Rewrite one legacy text element so the new renderer reproduces exactly
     * what the old one drew.
     *
     * @param array<string, mixed> $element
     * @return array<string, mixed>
     */
    public static function migrateLegacyElement(array $element, float $pageWidthMm): array
    {
        $fontSize = (float) ($element['fontSize'] ?? 16);
        $align = $element['textAlign'] ?? 'left';

        $element['x'] = round(self::legacyAnchorXMm((float) ($element['x'] ?? 0), $align, $pageWidthMm), 2);
        $element['y'] = round(self::legacyTextTopMm((float) ($element['y'] ?? 0), $fontSize), 2);

        return $element;
    }

    /**
     * Millimetres per pixel for a canvas of the given pixel width.
     *
     * Used by the designer so a point on screen is the same size as a point in
     * the produced PDF.
     */
    public static function pxPerMm(float $canvasWidthPx, float $pageWidthMm): float
    {
        return $pageWidthMm > 0 ? $canvasWidthPx / $pageWidthMm : 1.0;
    }
}
