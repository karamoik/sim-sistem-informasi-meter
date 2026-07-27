<?php

namespace App\Http\Controllers\Api;

use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = UserSetting::forUser($user->id)->get()->pluck('value', 'key');

        return $this->success($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['required'],
        ]);

        $user = $request->user();

        foreach ($validated['settings'] as $item) {
            UserSetting::set($user->id, $item['key'], $item['value']);
        }

        return $this->success(null, 'Pengaturan berhasil disimpan');
    }

    public function getNotificationPrefs(Request $request): JsonResponse
    {
        $user = $request->user();
        $prefs = UserSetting::get($user->id, 'notifications', [
            'offline_alert' => true,
            'daily_report' => false,
            'weekly_report' => true,
            'meter_warning' => true,
        ]);

        return $this->success($prefs);
    }

    public function updateNotificationPrefs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'offline_alert' => ['required', 'boolean'],
            'daily_report' => ['required', 'boolean'],
            'weekly_report' => ['required', 'boolean'],
            'meter_warning' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        UserSetting::set($user->id, 'notifications', $validated);

        return $this->success(null, 'Pengaturan notifikasi berhasil disimpan');
    }

    public function getAppearance(Request $request): JsonResponse
    {
        $user = $request->user();
        $appearance = UserSetting::get($user->id, 'appearance', [
            'theme' => 'system',
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'compact_mode' => false,
        ]);

        return $this->success($appearance);
    }

    public function updateAppearance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'language' => ['required', 'string', 'in:id,en'],
            'timezone' => ['required', 'string'],
            'compact_mode' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        UserSetting::set($user->id, 'appearance', $validated);

        return $this->success(null, 'Pengaturan tampilan berhasil disimpan');
    }
}
