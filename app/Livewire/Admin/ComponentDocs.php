<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.livewire-layout')]
#[Title('Component Documentation - AdminPro')]
class ComponentDocs extends Component
{
    public string $activeSection = 'buttons';
    public string $searchQuery = '';

    public array $sections = [
        'buttons' => ['label' => 'Buttons', 'icon' => 'fas fa-hand-pointer', 'count' => 2],
        'badges' => ['label' => 'Badges', 'icon' => 'fas fa-tag', 'count' => 1],
        'alerts' => ['label' => 'Alerts', 'icon' => 'fas fa-exclamation-circle', 'count' => 1],
        'forms' => ['label' => 'Form Inputs', 'icon' => 'fas fa-edit', 'count' => 3],
        'selects' => ['label' => 'Select & Dropdown', 'icon' => 'fas fa-list', 'count' => 3],
        'toggles' => ['label' => 'Toggle & Checkbox', 'icon' => 'fas fa-toggle-on', 'count' => 3],
        'cards' => ['label' => 'Cards', 'icon' => 'fas fa-square', 'count' => 3],
        'tables' => ['label' => 'Tables', 'icon' => 'fas fa-table', 'count' => 1],
        'navigation' => ['label' => 'Navigation', 'icon' => 'fas fa-compass', 'count' => 2],
        'feedback' => ['label' => 'Feedback', 'icon' => 'fas fa-comment', 'count' => 4],
        'media' => ['label' => 'Media & Upload', 'icon' => 'fas fa-image', 'count' => 2],
    ];

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function render()
    {
        return view('livewire.admin.component-docs');
    }
}
