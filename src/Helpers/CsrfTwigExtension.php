<?php

declare(strict_types=1);

namespace App\Helpers;

use Slim\Csrf\Guard;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfTwigExtension extends AbstractExtension implements \Twig\Extension\GlobalsInterface
{

    /**
     * @var Guard
     */
    protected $csrf;

    public function __construct(Guard $csrf)
    {
        $this->csrf = $csrf;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('csrf', [$this, 'csrfTwig']),
        ];
    }

    public function csrfTwig()
    {
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();

        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();

        //$lastKey = array_key_last($_SESSION['csrf']);
        //$lastValue = $_SESSION['csrf'][$lastKey];
        echo "<input type='hidden' name='". $csrfNameKey."' value='" .  $csrfName . "'>
        <input type='hidden' name='".$csrfValueKey."' value='" .  $csrfValue . "'>
        ";
        return;
    }

    public function getGlobals(): array
    {
        // CSRF token name and value
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();

        return [
            'csrf'   => [
                'keys' => [
                    'name'  => $csrfNameKey,
                    'value' => $csrfValueKey
                ],
                'name'  => $csrfName,
                'value' => $csrfValue
            ]
        ];
    }
}
