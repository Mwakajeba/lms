{{--
BREADCRUMB COMPONENT EXAMPLES

This file shows how to use the modern breadcrumb components in your Laravel Blade views.

Available Components:
1. <x-breadcrumbs> - Simple modern breadcrumb
    2. <x-breadcrumbs-with-icons> - Modern breadcrumb with icons
        --}}

        {{-- Example 1: Simple Modern Breadcrumb --}}
        <x-breadcrumbs :links="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Users', 'url' => route('users.index')],
        ['label' => 'User Details']
    ]" />

        {{-- Example 2: Breadcrumb with Icons --}}
        <x-breadcrumbs-with-icons :links="[
        ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
        ['label' => 'Users', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
        ['label' => 'User Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
    ]" />

        {{-- Example 3: Loan Products Breadcrumb --}}
        <x-breadcrumbs-with-icons :links="[
        ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
        ['label' => 'Loan Products', 'url' => route('loan-products.index'), 'icon' => 'bx bx-credit-card'],
        ['label' => 'Create Product', 'url' => '#', 'icon' => 'bx bx-plus-circle']
    ]" />

        {{-- Example 4: Settings Breadcrumb --}}
        <x-breadcrumbs-with-icons :links="[
        ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
        ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
        ['label' => 'User Preferences', 'url' => '#', 'icon' => 'bx bx-user-circle']
    ]" />

        {{-- Example 5: Reports Breadcrumb --}}
        <x-breadcrumbs-with-icons :links="[
        ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
        ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-bar-chart-alt-2'],
        ['label' => 'Loan Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
        ['label' => 'Monthly Summary', 'url' => '#', 'icon' => 'bx bx-calendar-check']
    ]" />

        {{--
        COMMON ICONS YOU CAN USE:

        Navigation:
        - bx bx-home (Home)
        - bx bx-arrow-back (Back)
        - bx bx-menu (Menu)

        Content:
        - bx bx-file (File)
        - bx bx-folder (Folder)
        - bx bx-document (Document)
        - bx bx-image (Image)

        Business:
        - bx bx-credit-card (Credit Card)
        - bx bx-dollar-circle (Money)
        - bx bx-user (User)
        - bx bx-group (Users)
        - bx bx-building (Building)
        - bx bx-store (Store)

        Actions:
        - bx bx-plus-circle (Add)
        - bx bx-edit (Edit)
        - bx bx-trash (Delete)
        - bx bx-save (Save)
        - bx bx-search (Search)

        Status:
        - bx bx-check-circle (Success)
        - bx bx-error-circle (Error)
        - bx bx-info-circle (Info)
        - bx bx-warning (Warning)

        Data:
        - bx bx-bar-chart-alt-2 (Chart)
        - bx bx-table (Table)
        - bx bx-list-ul (List)
        - bx bx-calendar (Calendar)
        - bx bx-time (Time)

        Settings:
        - bx bx-cog (Settings)
        - bx bx-user-circle (Profile)
        - bx bx-shield (Security)
        - bx bx-bell (Notifications)

        Current Page:
        - bx bx-current-location (Current Location)
        - bx bx-target-lock (Target)
        - bx bx-map-pin (Location)
        --}}