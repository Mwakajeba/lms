@extends('layouts.main')

@section('title', 'Bulk Email Invitation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Bulk Email', 'url' => '#', 'icon' => 'bx bx-envelope']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="bx bx-envelope me-2"></i>
                            SmartFinance System Invitation
                        </h4>
                        <p class="card-text text-muted">Send invitations to use SmartFinance system</p>
                    </div>
                    <div class="card-body">
                        <form id="bulkEmailForm">
                            @csrf
                            
                            <!-- Email Content -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="subject" class="form-label fw-bold">Email Subject</label>
                                    <input type="text" class="form-control form-control-lg" id="subject" name="subject" 
                                           value="Karibu SmartFinance - Mfumo wa Usimamizi wa Fedha" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="content" class="form-label fw-bold">Email Message</label>
                                    <textarea class="form-control" id="content" name="content" rows="12" required 
                                              placeholder="Enter your invitation message...">Samahani kwa kukutumia ujumbe huu bila ridhaa yako. Kampuni ya SAFCO FinTech, iliyoko Dodoma Mjini – Roma Complex, Mtaa wa Jimboni Roman Catholic - Cathedral, inapenda kukujulisha kuwa wanao mfumo wa Kisasa wa Taasisi za Kifedha uitwao SmartFinance – Version 2, ulioboreshwa mahsusi kwa ajili ya kusaidia taasisi za Microfinance katika maswala makuu yafuatayo:-

✅ Kudhibiti mikopo na marejesho kwa urahisi
✅ Kuandaa taarifa za kiuhasibu na kutoa Report za kupeleka Benki Kuu (BOT returns)
✅ Kupata taarifa sahihi za mikopo na ripoti mbali mbali kwa wakati.
✅ Usalama wa taarifa zako na uwezo wa watumiaji wengi kuweza kuingia kwenye Mfumo kwa wakati mmoja
✅ Urahisi wa kutumia mfumo hata kwa watumishi wapya
✅ Uwezo wa kusimamia matawi zaidi ya moja ndani ya mfumo mmoja Pamoja na Idadi ya watumiaji isiyo na Kikomo..

Tunatoa demo ya bure ili uweze kuujaribu na kujionea faida zake kabla ya kufanya maamuzi.

Ni mfumo uliotengenezwa na kuboreshwa na timu yetu, wenye uwezo wa kufanyiwa customization kulingana na mahitaji yenu maalum, na kwa sasa unatumiwa na zaidi ya taasisi 20 za Microfinance nchini.

👉 Jaribu demo kupitia kiungo hiki:
https://dev.smartsoft.co.tz
Username: 2556555778030
Password: 12345

Tafadhali jisikie huru kuwasiliana nasi kwa maelezo zaidi kupitia 0766 261 604.

Asante sana kwa muda wako, na samahani endapo ujumbe huu umekufikia kwa muda usiofaa.

Kwa heshima,
SAFCO FinTech Team
Watengenezaji wa SmartFinance.</textarea>
                                    <div class="form-text">This message will be sent to all recipients</div>
                                </div>
                            </div>

                            <!-- Recipients Info -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Recipients:</strong> Emails will be automatically selected from the <code>microfinances</code> database table.
                                        <br><strong>Available recipients:</strong> {{ $recipientCount ?? 0 }} contacts
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg" id="sendBtn">
                                        <i class="bx bx-send me-1"></i> Send Invitations
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="row mt-4" id="resultsSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bar-chart me-2"></i>
                            Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Logs Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-check me-2"></i>
                            Email Delivery Logs
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm active" data-filter="all">
                                    <i class="bx bx-list-ul me-1"></i> All
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" data-filter="sent">
                                    <i class="bx bx-check-circle me-1"></i> Sent
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-filter="failed">
                                    <i class="bx bx-x-circle me-1"></i> Failed
                                </button>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm" id="exportCsvBtn">
                                <i class="bx bx-download me-1"></i> Export CSV
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="refreshLogsBtn">
                                <i class="bx bx-refresh me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="emailLogsTable" class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Recipient</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Error</th>
                                        <th>Sent At</th>
                                        <th>Logged</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-2">Sending Invitations...</h5>
                <p class="text-muted mb-0">Please wait while we process your emails</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<script>
let emailLogsTable;
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    initializeEmailLogsTable();
    
    // Filter button handlers
    document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('btn-outline-secondary');
                btn.classList.remove('btn-secondary');
            });
            
            this.classList.add('active');
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-secondary');
            
            // Update filter
            currentFilter = this.getAttribute('data-filter');
            
            // Reload table with new filter
            emailLogsTable.ajax.reload();
        });
    });

    // Send emails
    document.getElementById('bulkEmailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            subject: formData.get('subject'),
            content: formData.get('content'),
            company_name: 'SmartFinance',
            use_queue: false
        };

        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        // Disable form
        document.getElementById('sendBtn').disabled = true;

        fetch('/settings/bulk-email/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON. Server might be redirecting or returning HTML.');
            }
            return response.json();
        })
        .then(data => {
            loadingModal.hide();
            
            if (data.success) {
                showAlert('🎉 Invitations sent successfully!', 'success');
                showResults(data.results);
            } else {
                showAlert(data.message || 'Failed to send invitations.', 'danger');
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                }
            }
        })
        .catch(error => {
            loadingModal.hide();
            showAlert('❌ An error occurred while sending invitations: ' + error.message, 'danger');
            console.error('Error:', error);
        })
        .finally(() => {
            document.getElementById('sendBtn').disabled = false;
        });
    });

    function showAlert(message, type) {
        // Create a simple alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function showResults(results) {
        const resultsSection = document.getElementById('resultsSection');
        const resultsContent = document.getElementById('resultsContent');
        
        const total = results.total || 0;
        const ok = (results.successful !== undefined) ? results.successful : (results.queued || 0);
        const failed = results.failed || 0;
        const labelOk = (results.successful !== undefined) ? 'Successful' : 'Queued';
        const successRate = total > 0 ? Math.round((ok / total) * 100) : 0;

        let html = '';
        html += '<div class="row g-3">';
        html += '  <div class="col-md-4">\
                    <div class="stat-card border-0 shadow-sm rounded p-3 h-100">\
                        <div class="d-flex align-items-center">\
                            <div class="stat-icon bg-primary text-white me-3"><i class="bx bx-group"></i></div>\
                            <div>\
                                <div class="text-muted small">Total</div>\
                                <div class="fs-4 fw-bold">' + total + '</div>\
                            </div>\
                        </div>\
                    </div>\
                  </div>';

        html += '  <div class="col-md-4">\
                    <div class="stat-card border-0 shadow-sm rounded p-3 h-100">\
                        <div class="d-flex align-items-center">\
                            <div class="stat-icon bg-success text-white me-3"><i class="bx bx-check-circle"></i></div>\
                            <div>\
                                <div class="text-muted small">' + labelOk + '</div>\
                                <div class="fs-4 fw-bold text-success">' + ok + '</div>\
                            </div>\
                        </div>\
                    </div>\
                  </div>';

        html += '  <div class="col-md-4">\
                    <div class="stat-card border-0 shadow-sm rounded p-3 h-100">\
                        <div class="d-flex align-items-center">\
                            <div class="stat-icon bg-danger text-white me-3"><i class="bx bx-x-circle"></i></div>\
                            <div>\
                                <div class="text-muted small">Failed</div>\
                                <div class="fs-4 fw-bold text-danger">' + failed + '</div>\
                            </div>\
                        </div>\
                    </div>\
                  </div>';
        html += '</div>';

        // Progress bar
        html += '<div class="mt-3">';
        html += '  <div class="d-flex justify-content-between align-items-center mb-1">';
        html += '    <span class="small text-muted">Success Rate</span>';
        html += '    <span class="small fw-semibold">' + successRate + '%</span>';
        html += '  </div>';
        html += '  <div class="progress" style="height: 10px;">\
                    <div class="progress-bar bg-success" role="progressbar" style="width: ' + successRate + '%;" aria-valuenow="' + successRate + '" aria-valuemin="0" aria-valuemax="100"></div>\
                  </div>';
        html += '</div>';

        if (results.errors && results.errors.length > 0) {
            html += '<div class="mt-4"><h6 class="text-danger">Errors</h6><ul class="list-unstyled mb-0">';
            results.errors.forEach(error => {
                html += '<li class="text-danger mb-1"><i class="bx bx-error me-1"></i>' + error + '</li>';
            });
            html += '</ul></div>';
        }
        
        resultsContent.innerHTML = html;
        resultsSection.style.display = 'block';
        
        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    // Initialize Email Logs DataTable
    function initializeEmailLogsTable() {
        emailLogsTable = $('#emailLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("settings.bulk-email.recipients") }}',
                type: 'GET',
                data: function(d) {
                    d.logs = true; // Flag to indicate we want logs data
                    d.status = currentFilter; // Add current filter
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '60px' },
                { 
                    data: null, 
                    name: 'recipient',
                    render: function(data, type, row) {
                        return '<div>' + (row.recipient_name || '-') + '</div>' +
                               '<small class="text-muted">' + row.recipient_email + '</small>';
                    }
                },
                { 
                    data: 'subject', 
                    name: 'subject',
                    render: function(data, type, row) {
                        return '<span title="' + data + '">' + (data.length > 30 ? data.substring(0, 30) + '...' : data) + '</span>';
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        const badgeClass = {
                            'sent': 'success',
                            'queued': 'info', 
                            'failed': 'danger'
                        }[data] || 'secondary';
                        return '<span class="badge bg-' + badgeClass + ' text-uppercase">' + data + '</span>';
                    }
                },
                { 
                    data: 'error_message', 
                    name: 'error_message',
                    render: function(data, type, row) {
                        if (!data) return '-';
                        return '<span title="' + data + '" class="text-danger">' + 
                               (data.length > 30 ? data.substring(0, 30) + '...' : data) + '</span>';
                    }
                },
                { 
                    data: 'sent_at', 
                    name: 'sent_at',
                    render: function(data, type, row) {
                        return data ? new Date(data).toLocaleString() : '-';
                    }
                },
                { 
                    data: 'created_at', 
                    name: 'created_at',
                    render: function(data, type, row) {
                        return new Date(data).toLocaleString();
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="bx bx-download me-1"></i> Export CSV',
                    className: 'btn btn-outline-success btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    },
                    action: function(e, dt, button, config) {
                        // Add filter to export
                        config.filename = 'email_logs_' + currentFilter + '_' + new Date().toISOString().split('T')[0];
                        
                        // Get filtered data
                        const filteredData = dt.ajax.params();
                        filteredData.status = currentFilter;
                        
                        // Create custom export
                        exportFilteredData(currentFilter);
                    }
                }
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: 'No email logs found',
                zeroRecords: 'No matching records found'
            }
        });
    }

    // Export CSV button
    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        exportFilteredData(currentFilter);
    });

    // Refresh button
    document.getElementById('refreshLogsBtn').addEventListener('click', function() {
        emailLogsTable.ajax.reload();
    });

    // Custom export function
    function exportFilteredData(filter) {
        const filename = 'email_logs_' + filter + '_' + new Date().toISOString().split('T')[0] + '.csv';
        
        // Show loading
        const originalText = document.getElementById('exportCsvBtn').innerHTML;
        document.getElementById('exportCsvBtn').innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Exporting...';
        document.getElementById('exportCsvBtn').disabled = true;
        
        // Fetch filtered data
        fetch('{{ route("settings.bulk-email.recipients") }}?logs=true&status=' + filter + '&export=true', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.data && data.data.length > 0) {
                // Convert to CSV
                const csvContent = convertToCSV(data.data);
                downloadCSV(csvContent, filename);
            } else {
                alert('No data to export for the selected filter.');
            }
        })
        .catch(error => {
            console.error('Export error:', error);
            alert('Error exporting data. Please try again.');
        })
        .finally(() => {
            document.getElementById('exportCsvBtn').innerHTML = originalText;
            document.getElementById('exportCsvBtn').disabled = false;
        });
    }

    // Convert data to CSV format
    function convertToCSV(data) {
        const headers = ['ID', 'Recipient Name', 'Recipient Email', 'Subject', 'Status', 'Error Message', 'Sent At', 'Created At'];
        const rows = data.map(item => [
            item.id,
            item.recipient_name || '',
            item.recipient_email,
            item.subject,
            item.status,
            item.error_message || '',
            item.sent_at ? new Date(item.sent_at).toLocaleString() : '',
            new Date(item.created_at).toLocaleString()
        ]);
        
        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(','))
            .join('\n');
            
        return csvContent;
    }

    // Download CSV file
    function downloadCSV(content, filename) {
        const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});
</script>
@endpush

@push('styles')
<style>
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.stat-card .stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-info code {
    background-color: #b8daff;
    color: #004085;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>
@endpush