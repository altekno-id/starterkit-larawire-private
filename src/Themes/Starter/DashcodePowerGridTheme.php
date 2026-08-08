<?php

namespace Altekno\StarterKit\Themes\Starter;

use PowerComponents\LivewirePowerGrid\Themes\Tailwind;

class DashcodePowerGridTheme extends Tailwind
{
    public function table(): array
    {
        return array_replace_recursive(parent::table(), [
            'layout' => [
                'base' => 'starter-pg-table align-middle inline-block min-w-full w-full',
                'div' => 'starter-pg-frame relative overflow-hidden',
                'table' => 'min-w-full starter-table',
                'container' => 'overflow-x-auto',
                'actions' => 'flex items-center justify-end gap-2',
            ],
            'header' => [
                'thead' => 'bg-slate-50',
                'th' => 'table-th whitespace-nowrap',
            ],
            'body' => [
                'tbody' => 'bg-white text-slate-600',
                'tr' => 'border-b border-slate-100 hover:bg-slate-50',
                'td' => 'table-td align-middle whitespace-nowrap',
                'tdFilters' => 'px-4 py-3 bg-slate-50',
                'tdEmpty' => 'p-6 text-center text-slate-500',
                'tdActionsContainer' => 'flex items-center justify-end gap-2',
            ],
        ]);
    }

    public function footer(): array
    {
        return array_replace(parent::footer(), [
            'select' => 'form-control starter-pg-page-size',
            'footer' => 'starter-pg-footer border-t border-slate-100',
            'footer_with_pagination' => 'flex w-full flex-col gap-3 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between',
        ]);
    }

    public function checkbox(): array
    {
        return array_replace(parent::checkbox(), [
            'th' => 'table-th text-center whitespace-nowrap',
            'base' => 'flex items-center justify-center',
            'input' => 'table-checkbox',
        ]);
    }

    public function filterBoolean(): array
    {
        return array_replace(parent::filterBoolean(), [
            'base' => 'starter-pg-filter starter-pg-filter-boolean',
            'select' => 'form-control',
        ]);
    }

    public function filterInputText(): array
    {
        return array_replace(parent::filterInputText(), [
            'base' => 'starter-pg-filter starter-pg-filter-text',
            'input' => 'form-control',
        ]);
    }

    public function filterMultiSelect(): array
    {
        return array_replace(parent::filterMultiSelect(), [
            'base' => 'starter-pg-filter starter-pg-filter-multiselect',
        ]);
    }

    public function filterNumber(): array
    {
        return array_replace(parent::filterNumber(), [
            'base' => 'starter-pg-filter starter-pg-filter-number',
            'input' => 'form-control',
        ]);
    }

    public function filterSelect(): array
    {
        return array_replace(parent::filterSelect(), [
            'base' => 'starter-pg-filter starter-pg-filter-select',
            'select' => 'form-control',
        ]);
    }

    public function searchBox(): array
    {
        return array_replace(parent::searchBox(), [
            'input' => 'form-control starter-pg-search',
            'iconSearch' => 'starter-pg-search-icon',
            'iconClose' => 'text-slate-400',
        ]);
    }
}
