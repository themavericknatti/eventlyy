<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Template Visual Layer Enablement
    |--------------------------------------------------------------------------
    | When set to false, views are rendered directly from views/ as standard.
    | When true, the TemplateManager resolves the active visual template and
    | looks up views in views/templates/{active}/ before falling back.
    */
    'enabled' => env('TEMPLATES_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    | The default active template identifier when no specific selection applies.
    */
    'default' => env('DEFAULT_TEMPLATE', 'minimal'),

    /*
    |--------------------------------------------------------------------------
    | Template Selection Strategy / Policy
    |--------------------------------------------------------------------------
    | Supported policies:
    | - 'session'    : Policy A - Selected once per session and stored in session.
    | - 'visit'      : Policy B - Selected per recognized visit (30-minute inactivity cookie).
    | - 'persistent' : Policy C - Persistent first-party cookie or account preference.
    | - 'random'     : Policy D - Randomly selects an available template per request (test/preview).
    | - 'fixed'      : Policy E - Always uses the configured 'default' template.
    */
    'strategy' => env('TEMPLATE_STRATEGY', 'fixed'),

    /*
    |--------------------------------------------------------------------------
    | Available Templates
    |--------------------------------------------------------------------------
    | White-listed template identifiers allowed in the system.
    */
    'available' => [
        'minimal',
        'premium',
        'creative',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Template
    |--------------------------------------------------------------------------
    | Used if a requested or assigned template is invalid, disabled, or missing.
    */
    'fallback' => 'minimal',

    /*
    |--------------------------------------------------------------------------
    | Explicit Overrides & Customer Selection
    |--------------------------------------------------------------------------
    | allow_overrides: Allows controllers / code to call app()->templates->setOverride(...)
    | allow_customer_selection: Allows query parameter (?template=xxx) or user switcher
    */
    'allow_overrides' => true,
    'allow_customer_selection' => false,

    /*
    |--------------------------------------------------------------------------
    | Cookie Settings for Visit & Persistent Policies
    |--------------------------------------------------------------------------
    */
    'cookie' => [
        'visit_name'      => 'ace_template_visit',
        'visit_lifetime'  => 1800,        // 30 minutes inactivity
        'persist_name'    => 'ace_template_visitor',
        'persist_lifetime'=> 31536000,    // 1 year
    ],
];
