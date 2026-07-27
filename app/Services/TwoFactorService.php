<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeUrl(User $user, string $secret): string
    {
        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(256),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svgString = $writer->writeString($otpauthUrl);

        return 'data:image/svg+xml;base64,' . base64_encode($svgString);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code, 8);
    }
}
