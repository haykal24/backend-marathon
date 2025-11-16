<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['benefits']) && is_string($data['benefits'])) {
            $data['benefits'] = array_filter(array_map('trim', explode("\n", $data['benefits'])));
        }

        // Auto-fill SEO jika kosong
        if (empty($data['seo_title']) && !empty($data['title'])) {
            $data['seo_title'] = $data['title'];
        }
        if (empty($data['seo_description']) && !empty($data['description'])) {
            $data['seo_description'] = static::extractTextFromRichEditor($data['description']);
        }

        return $data;
    }

    protected static function extractTextFromRichEditor($content): string
    {
        if (empty($content)) {
            return '';
        }

        // Jika sudah string HTML
        if (is_string($content)) {
            return strip_tags($content);
        }

        // Jika array TipTap structure
        if (is_array($content)) {
            $text = '';
            if (isset($content['content']) && is_array($content['content'])) {
                foreach ($content['content'] as $node) {
                    if (isset($node['content']) && is_array($node['content'])) {
                        foreach ($node['content'] as $leaf) {
                            if (isset($leaf['text']) && is_string($leaf['text'])) {
                                $text .= $leaf['text'] . ' ';
                            }
                        }
                    } elseif (isset($node['text']) && is_string($node['text'])) {
                        $text .= $node['text'] . ' ';
                    }
                }
            }
            return trim($text) ?: '';
        }

        return '';
    }
}

