<?php

return [
    /**
     * Toggle if plugin should register its dedicated dashboard page.
     */
    'dedicated_dashboard' => true,

    /**
     * Navigation icon for the dedicated dashboard.
     */
    'dashboard_icon' => 'heroicon-m-chart-bar',

    /**
     * Widget visibility.
     *
     * Set `filament_dashboard` to true to surface the widget on the default Filament dashboard.
     * Keep `global` true to allow usage inside custom dashboards or pages.
     */
    'page_views' => [
        'filament_dashboard' => false,
        'global' => true,
    ],
    'visitors' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'active_users_one_day' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'active_users_seven_day' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'active_users_twenty_eight_day' => [
        'filament_dashboard' => false,
        'global' => false,
    ],

    'sessions' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'sessions_duration' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'sessions_by_country' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'sessions_by_device' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'most_visited_pages' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    'top_referrers_list' => [
        'filament_dashboard' => false,
        'global' => true,
    ],

    /**
     * Trajectory Icons
     */
    'trending_up_icon' => 'heroicon-o-arrow-trending-up',
    'trending_down_icon' => 'heroicon-o-arrow-trending-down',
    'trending_steady_icon' => 'heroicon-o-arrows-right-left',

    /**
     * Trajectory Colors
     */
    'trending_up_color' => 'success',
    'trending_down_color' => 'danger',
    'trending_steady_color' => 'gray',
];