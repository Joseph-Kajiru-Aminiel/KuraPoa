<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SponsorLogoService
{
    private const SVG_ELEMENTS = [
        'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'defs', 'linearGradient', 'radialGradient', 'stop', 'clipPath', 'mask', 'use',
        'symbol', 'title', 'desc', 'text', 'tspan',
    ];

    private const SVG_ATTRIBUTES = [
        'xmlns', 'viewBox', 'width', 'height', 'fill', 'fill-rule', 'stroke', 'stroke-width',
        'stroke-linecap', 'stroke-linejoin', 'opacity', 'transform', 'd', 'x', 'y', 'x1',
        'x2', 'y1', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'points', 'offset', 'stop-color',
        'stop-opacity', 'id', 'href', 'clip-path', 'mask', 'gradientUnits',
        'gradientTransform', 'preserveAspectRatio', 'font-size', 'text-anchor',
    ];

    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $basename = bin2hex(random_bytes(16));
        $path = "sponsors/{$basename}.{$extension}";

        if ($extension === 'svg') {
            $stored = Storage::disk('public')->put($path, $this->sanitizeSvg($file->getRealPath()));

            return $stored ? $path : throw new RuntimeException('Unable to store the uploaded sponsor logo.');
        }

        $storedPath = $file->storeAs('sponsors', "{$basename}.{$extension}", 'public');

        return $storedPath ?: throw new RuntimeException('Unable to store the uploaded sponsor logo.');
    }

    private function sanitizeSvg(string $path): string
    {
        if (! class_exists(DOMDocument::class)) {
            throw ValidationException::withMessages(['logo' => __('Usaidizi wa SVG haupatikani kwenye seva hii.')]);
        }

        $contents = file_get_contents($path);
        if (! is_string($contents) || preg_match('/<!DOCTYPE|<!ENTITY/i', $contents)) {
            throw ValidationException::withMessages(['logo' => __('Faili la SVG si halali.')]);
        }

        $document = new DOMDocument;
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML($contents, LIBXML_NONET | LIBXML_NOBLANKS);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $root = $document->documentElement;
        if (! $loaded || ! $root || strtolower($root->localName) !== 'svg') {
            throw ValidationException::withMessages(['logo' => __('Faili la SVG si halali.')]);
        }

        foreach ($document->getElementsByTagName('*') as $element) {
            if (! $element instanceof DOMElement || ! in_array($element->localName, self::SVG_ELEMENTS, true)) {
                throw ValidationException::withMessages(['logo' => __('SVG ina vipengele visivyoruhusiwa.')]);
            }

            foreach ($element->attributes as $attribute) {
                if (! in_array($attribute->nodeName, self::SVG_ATTRIBUTES, true)
                    || (str_contains(strtolower($attribute->value), 'url(')
                        && ! preg_match('/^url\\(#[A-Za-z0-9_-]+\\)$/', $attribute->value))
                    || ($attribute->nodeName === 'href' && ! str_starts_with($attribute->value, '#'))) {
                    throw ValidationException::withMessages(['logo' => __('SVG ina sifa zisizoruhusiwa.')]);
                }
            }
        }

        return $document->saveXML($root) ?: throw ValidationException::withMessages(['logo' => __('Faili la SVG halikuweza kusafishwa.')]);
    }
}
