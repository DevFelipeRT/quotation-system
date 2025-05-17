<?php

namespace Tests\Controllers;

use App\Kernel\Infrastructure\SessionKernel;
use Tests\Decorators\CustomSessionDataDecorator;

/**
 * Example controller demonstrating storage of custom data in session.
 */
class SessionTestController
{
    private SessionKernel $sessionKernel;

    public function __construct(SessionKernel $sessionKernel)
    {
        $this->sessionKernel = $sessionKernel;
    }

    public function setFavoriteColor(string $color): void
    {
        $originalData = $this->sessionKernel->getData();
        $decorated = new CustomSessionDataDecorator ($originalData, ['favorite_color' => $color]);
        $this->sessionKernel->setData($decorated);
        echo "Favorite color set to: {$color}\n";
    }

    public function getFavoriteColor(): void
    {
        $data = $this->sessionKernel->getData();

        // Use decorator method, or fallback
        if ($data instanceof CustomSessionDataDecorator) {
            $favorite = $data->getCustomField('favorite_color', '(not set)');
        } elseif (isset($data->toArray()['custom_fields']['favorite_color'])) {
            $favorite = $data->toArray()['custom_fields']['favorite_color'];
        } else {
            $favorite = '(not set)';
        }
        echo "Favorite color is: {$favorite}\n";
    }
}
