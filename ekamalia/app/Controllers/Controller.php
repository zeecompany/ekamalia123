<?php
declare(strict_types=1);

namespace App\Controllers;

class Controller
{
    /** controllers may override (dashboard, admin, pos) */
    protected string $defaultLayout = 'layouts/main';

    protected function view(string $tpl, array $data = [], ?string $layout = null): void
    {
        $layout = $layout ?? ($data['layout'] ?? null) ?? $this->defaultLayout;
        \view($tpl, $data, $layout);
    }
    protected function json(array $data, int $status = 200): never
    {
        \json_out($data, $status);
    }
    protected function notFound(): never
    {
        \not_found();
    }
}
