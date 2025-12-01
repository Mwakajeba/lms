@extends('layouts.main')

@section('title', 'Laravel Logs')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">

            <!-- Breadcrumbs -->
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Laravel Logs', 'url' => '#', 'icon' => 'bx bx-bug']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">LARAVEL LOGS</h6>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-warning btn-sm" id="clearAllLogs">
                        <i class="bx bx-eraser"></i> Clear All Logs
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">
                        <i class="bx bx-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <hr />

            <!-- Log Files Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="logTabs" role="tablist">
                                @foreach($logFiles as $index => $file)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                            id="{{ str_replace('.', '-', $file) }}-tab" data-bs-toggle="tab"
                                            data-bs-target="#{{ str_replace('.', '-', $file) }}" type="button" role="tab">
                                            {{ $file }}
                                            <span class="badge bg-secondary ms-2">{{ count($logs[$file] ?? []) }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="logTabsContent">
                                @foreach($logFiles as $index => $file)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                        id="{{ str_replace('.', '-', $file) }}" role="tabpanel">

                                        @if(isset($logs[$file]) && count($logs[$file]) > 0)
                                            <div class="log-container">
                                                @foreach($logs[$file] as $log)
                                                    <div class="log-entry {{ $log['is_error'] ? 'log-error' : 'log-normal' }}"
                                                        data-level="{{ $log['level'] }}">
                                                        <div class="log-header">
                                                            <div class="log-timestamp">{{ $log['timestamp'] }}</div>
                                                            <div class="log-level">
                                                                <span
                                                                    class="badge 
                                                                                                                                        @if($log['level'] === 'ERROR' || $log['level'] === 'CRITICAL' || $log['level'] === 'EMERGENCY' || $log['level'] === 'ALERT')
                                                                                                                                            bg-danger
                                                                                                                                        @elseif($log['level'] === 'WARNING')
                                                                                                                                            bg-warning
                                                                                                                                        @elseif($log['level'] === 'INFO')
                                                                                                                                            bg-info
                                                                                                                                        @elseif($log['level'] === 'DEBUG')
                                                                                                                                            bg-secondary
                                                                                                                                        @else
                                                                                                                                            bg-primary
                                                                                                                                        @endif">
                                                                    {{ $log['level'] }}
                                                                </span>
                                                            </div>
                                                            <div class="log-environment">{{ $log['environment'] }}</div>
                                                        </div>

                                                        <div class="log-message">
                                                            <pre class="log-message-content">{{ $log['message'] }}</pre>
                                                        </div>

                                                        @if(!empty($log['context']))
                                                            <div class="log-context">
                                                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#context-{{ md5($log['timestamp'] . $log['message']) }}"
                                                                    aria-expanded="false">
                                                                    <i class="bx bx-chevron-down"></i> Context
                                                                </button>
                                                                <div class="collapse mt-2"
                                                                    id="context-{{ md5($log['timestamp'] . $log['message']) }}">
                                                                    <div class="card card-body">
                                                                        <pre class="log-context-content">{{ $log['context'] }}</pre>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if(!empty($log['stack_trace']))
                                                            <div class="log-stack-trace">
                                                                <button class="btn btn-sm btn-outline-danger" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#stack-{{ md5($log['timestamp'] . $log['message']) }}"
                                                                    aria-expanded="false">
                                                                    <i class="bx bx-chevron-down"></i> Stack Trace
                                                                </button>
                                                                <div class="collapse mt-2"
                                                                    id="stack-{{ md5($log['timestamp'] . $log['message']) }}">
                                                                    <div class="card card-body">
                                                                        <pre class="log-stack-content">{{ $log['stack_trace'] }}</pre>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-5">
                                                <i class="bx bx-file-blank" style="font-size: 3rem;"></i>
                                                <p class="mt-3">No logs found in {{ $file }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .log-container {
            max-height: 70vh;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            background-color: #f8f9fa;
        }

        .log-entry {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.375rem;
            border-left: 4px solid #6c757d;
            background-color: white;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .log-entry.log-error {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }

        .log-entry.log-normal {
            border-left-color: #28a745;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .log-timestamp {
            font-weight: 600;
            color: #495057;
            font-family: 'Courier New', monospace;
        }

        .log-level .badge {
            font-size: 0.75rem;
        }

        .log-environment {
            font-size: 0.875rem;
            color: #6c757d;
            background-color: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .log-message {
            margin-bottom: 0.5rem;
        }

        .log-message-content {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            line-height: 1.4;
            color: #212529;
        }

        .log-context,
        .log-stack-trace {
            margin-top: 0.5rem;
        }

        .log-context-content,
        .log-stack-content {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            line-height: 1.3;
            color: #495057;
            background-color: #f8f9fa;
            padding: 0.75rem;
            border-radius: 0.25rem;
            border: 1px solid #e9ecef;
        }

        .log-stack-content {
            background-color: #fff5f5;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6c757d;
        }

        .nav-tabs .nav-link.active {
            color: #495057;
            border-bottom-color: #0d6efd;
            background-color: transparent;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: #0d6efd;
            color: #495057;
        }

        .clear-specific-log {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
        }

        /* Scrollbar styling */
        .log-container::-webkit-scrollbar {
            width: 8px;
        }

        .log-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .log-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .log-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .log-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .log-timestamp,
            .log-level,
            .log-environment {
                margin-bottom: 0.25rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Clear all logs
            $('#clearAllLogs').click(function () {
                if (confirm('Are you sure you want to clear all log files? This will empty all log files but keep the files themselves.')) {
                    const button = $(this);
                    const originalText = button.html();

                    // Show loading state
                    button.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Clearing...');

                    $.ajax({
                        url: '{{ route("laravel-logs.clear") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);
                                setTimeout(function () {
                                    location.reload();
                                }, 1500);
                            }
                        },
                        error: function (xhr) {
                            toastr.error('Error clearing logs: ' + (xhr.responseJSON?.message || 'Unknown error'));
                            button.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });


            // Auto-refresh every 30 seconds
            setInterval(function () {
                // Only refresh if no modals are open and user is on the logs page
                if (!document.querySelector('.modal.show')) {
                    location.reload();
                }
            }, 30000);

            // Highlight error logs
            $('.log-entry.log-error').each(function () {
                $(this).addClass('animate__animated animate__pulse');
            });
        });
    </script>
@endpush