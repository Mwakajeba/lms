@props(['links'])

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb-modern">
        @foreach($links as $index => $link)
            @if($index === count($links) - 1)
                <!-- Current/Last item -->
                <li class="breadcrumb-item breadcrumb-item-current" aria-current="page">
                    <span class="breadcrumb-text">{{ $link['label'] }}</span>
                </li>
            @else
                <!-- Link item -->
                <li class="breadcrumb-item">
                    <a href="{{ $link['url'] }}" class="breadcrumb-link">
                        <span class="breadcrumb-text">{{ $link['label'] }}</span>
                    </a>
                    <span class="breadcrumb-separator">
                        <i class="bx bx-chevron-right"></i>
                    </span>
                </li>
            @endif
        @endforeach
    </ol>
</nav>

<style>
    .breadcrumb-modern {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        background: transparent;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        color: #6c757d;
        transition: all 0.2s ease;
    }

    .breadcrumb-item:not(:last-child) {
        margin-right: 0.5rem;
    }

    .breadcrumb-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #6c757d;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
 
    .breadcrumb-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(13, 110, 253, 0.1), transparent);
        transition: left 0.5s ease;
    }

    .breadcrumb-link:hover {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
    }

    .breadcrumb-link:hover::before {
        left: 100%;
    }

    .breadcrumb-item-current {
        color: #495057;
        font-weight: 600;
    }

    .breadcrumb-item-current .breadcrumb-text {
        padding: 0.5rem 0.75rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .breadcrumb-separator {
        display: flex;
        align-items: center;
        color: #adb5bd;
        margin-left: 0.5rem;
        font-size: 0.75rem;
    }

    .breadcrumb-separator i {
        font-size: 0.875rem;
    }

    .breadcrumb-text {
        font-weight: inherit;
        white-space: nowrap;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .breadcrumb-modern {
            font-size: 0.8rem;
            flex-wrap: wrap;
        }

        .breadcrumb-item {
            margin-bottom: 0.25rem;
        }

        .breadcrumb-link,
        .breadcrumb-item-current .breadcrumb-text {
            padding: 0.375rem 0.5rem;
        }

        .breadcrumb-separator {
            margin-left: 0.25rem;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .breadcrumb-item {
            color: #adb5bd;
        }

        .breadcrumb-link {
            color: #adb5bd;
        }

        .breadcrumb-link:hover {
            color: #6ea8fe;
            background-color: rgba(110, 168, 254, 0.1);
        }

        .breadcrumb-item-current {
            color: #e9ecef;
        }

        .breadcrumb-item-current .breadcrumb-text {
            background: linear-gradient(135deg, #343a40 0%, #495057 100%);
            border-color: #6c757d;
        }

        .breadcrumb-separator {
            color: #6c757d;
        }
    }

    /* Animation for page load */
    .breadcrumb-item {
        animation: fadeInUp 0.3s ease forwards;
        opacity: 0;
        transform: translateY(10px);
    }

    .breadcrumb-item:nth-child(1) {
        animation-delay: 0.1s;
    }

    .breadcrumb-item:nth-child(2) {
        animation-delay: 0.2s;
    }

    .breadcrumb-item:nth-child(3) {
        animation-delay: 0.3s;
    }

    .breadcrumb-item:nth-child(4) {
        animation-delay: 0.4s;
    }

    .breadcrumb-item:nth-child(5) {
        animation-delay: 0.5s;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>