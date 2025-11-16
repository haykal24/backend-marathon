<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $settings = SiteSetting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->keyBy('key');

        $allSettings = collect(SiteSetting::KEY_DEFINITIONS)
            ->mapWithKeys(function (array $definition, string $key) use ($settings) {
                if ($settings->has($key)) {
                    return [
                        $key => $this->formatSetting($settings->get($key)),
                    ];
                }

                return [
                    $key => $this->formatDefaultSetting($key, $definition),
                ];
            });

        // Standard API response: { success, message, data: {...settings...}, meta: {...} }
        return $this->successResponse(
            $allSettings->toArray(),
            'Site settings retrieved successfully',
            200,
            [
                'groups' => SiteSetting::groupOptions(),
            ]
        );
    }

    public function show(string $key): JsonResponse
    {
        $setting = SiteSetting::where('key', $key)->firstOrFail();

        return $this->successResponse($this->formatSetting($setting));
    }

    protected function formatDefaultSetting(string $key, array $definition): array
    {
        $group = $definition['group'] ?? 'general';

        return [
            'key' => $key,
            'label' => $definition['label'] ?? $key,
            'value' => $definition['default'] ?? null,
            'type' => $definition['type'] ?? 'text',
            'group' => $group,
            'group_label' => SiteSetting::groupLabel($group),
            'description' => $definition['description'] ?? null,
        ];
    }

    protected function formatSetting(SiteSetting $setting): array
    {
        $definition = SiteSetting::getDefinition($setting->key);

        $value = match ($setting->type) {
            'image' => $setting->getImageValue(),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };

        return [
            'key' => $setting->key,
            'label' => $definition['label'] ?? $setting->key_label,
            'value' => $value,
            'type' => $setting->type,
            'group' => $setting->group,
            'group_label' => SiteSetting::groupLabel($setting->group),
            'description' => $setting->description ?? ($definition['description'] ?? null),
        ];
    }
}