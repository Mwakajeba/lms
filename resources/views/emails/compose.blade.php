@extends('layouts.app')

@section('title', 'Compose Email')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0"><i class="bx bx-envelope me-2"></i>Compose Email</h5>
                        <small class="text-muted">Send emails to microfinance institutions</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-info" onclick="testEmail()">
                            <i class="bx bx-test-tube me-1"></i> Test Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="loadRecipients()">
                            <i class="bx bx-refresh me-1"></i> Refresh Recipients
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="emailForm">
                        @csrf
                        
                        <!-- Email Subject -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       placeholder="Enter email subject" required>
                            </div>
                        </div>

                        <!-- Recipients Selection -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Recipients</label>
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Select Recipients</h6>
                                            <small class="text-muted">Choose which microfinance institutions to send emails to</small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                                <i class="bx bx-check-square me-1"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNone()">
                                                <i class="bx bx-square me-1"></i> Select None
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        <div id="recipientsList">
                                            <div class="text-center py-3">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">Loading recipients...</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">
                                            <span id="selectedCount">0</span> recipients selected
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Content -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="content" class="form-label">Email Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="10" 
                                          placeholder="Enter your email content here..." required></textarea>
                                <div class="form-text">
                                    <small>You can use HTML tags for formatting. The recipient's name will be automatically inserted.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Send Options -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="send_type" class="form-label">Send Method</label>
                                <select class="form-select" id="send_type" name="send_type" required>
                                    <option value="immediate">Send Immediately</option>
                                    <option value="queue">Queue for Background Processing</option>
                                </select>
                                <div class="form-text">
                                    <small>Immediate: Send emails right away. Queue: Process emails in background (recommended for large lists).</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preview</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-info" onclick="previewEmail()">
                                        <i class="bx bx-show me-1"></i> Preview Email
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" id="sendBtn">
                                        <i class="bx bx-send me-1"></i> Send Emails
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="emailPreview" style="border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let recipients = [];

// Load recipients on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecipients();
});

// Load recipients from server
function loadRecipients() {
    fetch('{{ route("emails.microfinances") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                recipients = data.data;
                displayRecipients();
            } else {
                showAlert('Failed to load recipients', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading recipients:', error);
            showAlert('Error loading recipients', 'error');
        });
}

// Display recipients in the list
function displayRecipients() {
    const container = document.getElementById('recipientsList');
    
    if (recipients.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-muted">No recipients found</div>';
        return;
    }
    
    let html = '';
    recipients.forEach(recipient => {
        html += `
            <div class="form-check mb-2">
                <input class="form-check-input recipient-checkbox" type="checkbox" 
                       value="${recipient.id}" id="recipient_${recipient.id}">
                <label class="form-check-label" for="recipient_${recipient.id}">
                    <strong>${recipient.name}</strong> 
                    <small class="text-muted">(${recipient.email})</small>
                </label>
            </div>
        `;
    });
    
    container.innerHTML = html;
    updateSelectedCount();
    
    // Add event listeners to checkboxes
    document.querySelectorAll('.recipient-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
}

// Update selected count
function updateSelectedCount() {
    const selected = document.querySelectorAll('.recipient-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

// Select all recipients
function selectAll() {
    document.querySelectorAll('.recipient-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

// Select none
function selectNone() {
    document.querySelectorAll('.recipient-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

// Test email functionality
function testEmail() {
    Swal.fire({
        title: 'Sending Test Email...',
        text: 'Please wait while we send a test email.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('{{ route("emails.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire('Success!', data.message, 'success');
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error!', 'Failed to send test email', 'error');
        console.error('Error:', error);
    });
}

// Preview email
function previewEmail() {
    const subject = document.getElementById('subject').value;
    const content = document.getElementById('content').value;
    
    if (!subject || !content) {
        showAlert('Please fill in subject and content first', 'warning');
        return;
    }
    
    // Create preview content
    const previewContent = `
        <div style="padding: 20px;">
            <h4>${subject}</h4>
            <hr>
            <div style="margin: 20px 0;">
                <p><strong>Dear [Recipient Name],</strong></p>
                <div style="white-space: pre-wrap;">${content}</div>
            </div>
            <hr>
            <p><small class="text-muted">This is how the email will appear to recipients.</small></p>
        </div>
    `;
    
    document.getElementById('emailPreview').innerHTML = previewContent;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// Reset form
function resetForm() {
    document.getElementById('emailForm').reset();
    selectNone();
}

// Show alert
function showAlert(message, type = 'info') {
    Swal.fire({
        title: type === 'error' ? 'Error!' : type === 'warning' ? 'Warning!' : 'Info!',
        text: message,
        icon: type,
        confirmButtonText: 'OK'
    });
}

// Handle form submission
document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const selectedRecipients = Array.from(document.querySelectorAll('.recipient-checkbox:checked'))
        .map(checkbox => parseInt(checkbox.value));
    
    if (selectedRecipients.length === 0) {
        showAlert('Please select at least one recipient', 'warning');
        return;
    }
    
    // Add selected recipients to form data
    selectedRecipients.forEach(id => {
        formData.append('recipients[]', id);
    });
    
    // Show confirmation
    Swal.fire({
        title: 'Send Emails?',
        text: `Are you sure you want to send emails to ${selectedRecipients.length} recipient(s)?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Send!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            sendEmails(formData);
        }
    });
});

// Send emails
function sendEmails(formData) {
    const sendBtn = document.getElementById('sendBtn');
    const originalText = sendBtn.innerHTML;
    
    sendBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Sending...';
    sendBtn.disabled = true;
    
    fetch('{{ route("emails.send") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
        
        if (data.success) {
            Swal.fire({
                title: 'Emails Sent!',
                html: `
                    <div class="text-start">
                        <p><strong>Total:</strong> ${data.results.total}</p>
                        <p><strong>Successful:</strong> ${data.results.successful || data.results.queued || 0}</p>
                        <p><strong>Failed:</strong> ${data.results.failed}</p>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            resetForm();
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        sendBtn.innerHTML = originalText;
        sendBtn.disabled = false;
        Swal.fire('Error!', 'Failed to send emails', 'error');
        console.error('Error:', error);
    });
}
</script>
@endsection
