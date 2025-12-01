@extends('layouts.main')

@section('title', 'AI Assistant')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'AI Assistant', 'url' => '#', 'icon' => 'bx bx-bot']
        ]" />
        <h6 class="mb-0 text-uppercase">AI ASSISTANT</h6>
        <hr/>

        <div class="row">
            <!-- AI Chat Interface -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bot me-2"></i>AI Assistant
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Chat Messages -->
                        <div id="chat-messages" class="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 0.375rem; padding: 1rem; margin-bottom: 1rem; background-color: #f8f9fa;">
                            <div class="text-center text-muted">
                                <i class="bx bx-bot fs-1"></i>
                                <p class="mt-2">Hello! I'm your AI assistant. I can help you generate reports, analyze data, and more. Just ask me what you need!</p>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <form id="chat-form" class="d-flex gap-2">
                            @csrf
                            <div class="flex-grow-1">
                                <input type="text" id="user-input" class="form-control" placeholder="Ask me anything... (e.g., 'Generate a sales report for this month', 'Create a financial summary', 'Analyze customer data')" required>
                            </div>
                            <button type="submit" class="btn btn-primary" id="send-btn">
                                <i class="bx bx-send"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & History -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary btn-sm" id="test-connection">
                                <i class="bx bx-test-tube me-1"></i> Test Connection
                            </button>
                            
                            <h6 class="mt-3 mb-2 text-muted">📊 Business Reports</h6>
                            <button class="btn btn-outline-primary btn-sm quick-action" data-prompt="Generate a monthly sales report">
                                <i class="bx bx-chart me-1"></i> Sales Report
                            </button>
                            <button class="btn btn-outline-success btn-sm quick-action" data-prompt="Create a financial summary for this quarter">
                                <i class="bx bx-dollar-circle me-1"></i> Financial Summary
                            </button>
                            <button class="btn btn-outline-info btn-sm quick-action" data-prompt="Analyze customer data and provide insights">
                                <i class="bx bx-user me-1"></i> Customer Analysis
                            </button>
                            <button class="btn btn-outline-warning btn-sm quick-action" data-prompt="Generate an inventory report">
                                <i class="bx bx-package me-1"></i> Inventory Report
                            </button>
                            <button class="btn btn-outline-danger btn-sm quick-action" data-prompt="Create a profit and loss statement">
                                <i class="bx bx-trending-up me-1"></i> P&L Statement
                            </button>
                            
                            <h6 class="mt-3 mb-2 text-muted">🛠️ System Help</h6>
                            <button class="btn btn-outline-purple btn-sm quick-action" data-prompt="How do I add a new user?">
                                <i class="bx bx-user-plus me-1"></i> Add User Guide
                            </button>
                            <button class="btn btn-outline-purple btn-sm quick-action" data-prompt="Help me set up a backup">
                                <i class="bx bx-data me-1"></i> Backup Guide
                            </button>
                            <button class="btn btn-outline-purple btn-sm quick-action" data-prompt="How to manage company settings?">
                                <i class="bx bx-building me-1"></i> Company Setup
                            </button>
                            <button class="btn btn-outline-purple btn-sm quick-action" data-prompt="What permissions do I need?">
                                <i class="bx bx-shield me-1"></i> Permissions Guide
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-download me-2"></i>Export Options
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-sm" id="export-pdf" disabled>
                                <i class="bx bx-file-pdf me-1"></i> Export as PDF
                            </button>
                            <button class="btn btn-info btn-sm" id="export-excel" disabled>
                                <i class="bx bx-file me-1"></i> Export as Excel
                            </button>
                            <button class="btn btn-secondary btn-sm" id="copy-to-clipboard" disabled>
                                <i class="bx bx-copy me-1"></i> Copy to Clipboard
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Conversations -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-history me-2"></i>Recent Conversations
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="conversation-history">
                            <div class="text-center text-muted">
                                <small>No recent conversations</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>AI is thinking...</h6>
                <p class="text-muted mb-0">Please wait while I process your request.</p>
            </div>
        </div>
    </div>
</div>

<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
</footer>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    const exportPdf = document.getElementById('export-pdf');
    const exportExcel = document.getElementById('export-excel');
    const copyToClipboard = document.getElementById('copy-to-clipboard');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    let currentResponse = '';
    let conversationHistory = [];

    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = userInput.value.trim();
        if (!message) return;

        // Add user message to chat
        addMessage('user', message);
        userInput.value = '';
        sendBtn.disabled = true;

        // Show loading modal
        loadingModal.show();

        // Send request to AI using FormData
        const formData = new FormData();
        formData.append('message', message);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        

        
        fetch('{{ route("settings.ai.chat") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            loadingModal.hide();
            sendBtn.disabled = false;
            
            if (data.success) {
                currentResponse = data.response;
                addMessage('ai', data.response);
                enableExportButtons();
                addToHistory(message, data.response);
            } else {
                addMessage('ai', 'Sorry, I encountered an error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            loadingModal.hide();
            sendBtn.disabled = false;
            addMessage('ai', 'Sorry, I encountered an error: ' + error.message);
        });
    });

    // Test connection button
    document.getElementById('test-connection').addEventListener('click', function() {
        fetch('{{ route("settings.ai.test") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('CSRF Token:', data.csrf_token);
                console.log('Test Response:', data);
                showToast(data.message || 'Connection test successful!', 'success');
            })
            .catch(error => {
                console.error('Test connection error:', error);
                showToast('Connection test failed: ' + error.message, 'error');
            });
    });

    // Quick action buttons
    document.querySelectorAll('.quick-action').forEach(btn => {
        btn.addEventListener('click', function() {
            const prompt = this.getAttribute('data-prompt');
            userInput.value = prompt;
            chatForm.dispatchEvent(new Event('submit'));
        });
    });

    // Export buttons
    exportPdf.addEventListener('click', function() {
        if (currentResponse) {
            exportAsPDF(currentResponse);
        }
    });

    exportExcel.addEventListener('click', function() {
        if (currentResponse) {
            exportAsExcel(currentResponse);
        }
    });

    copyToClipboard.addEventListener('click', function() {
        if (currentResponse) {
            navigator.clipboard.writeText(currentResponse).then(() => {
                showToast('Copied to clipboard!', 'success');
            });
        }
    });

    function addMessage(sender, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message mb-3`;
        
        const icon = sender === 'user' ? 'bx-user' : 'bx-bot';
        const bgClass = sender === 'user' ? 'bg-primary text-white' : 'bg-light';
        const alignClass = sender === 'user' ? 'text-end' : 'text-start';
        
        messageDiv.innerHTML = `
            <div class="${alignClass}">
                <div class="d-inline-block ${bgClass} p-3 rounded" style="max-width: 80%;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bx ${icon} me-2"></i>
                        <small class="fw-bold">${sender === 'user' ? 'You' : 'AI Assistant'}</small>
                    </div>
                    <div class="message-content">${formatMessage(message)}</div>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function formatMessage(message) {
        // Convert markdown-like formatting to HTML
        return message
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>')
            .replace(/```(.*?)```/gs, '<pre class="bg-dark text-light p-2 rounded"><code>$1</code></pre>');
    }

    function enableExportButtons() {
        exportPdf.disabled = false;
        exportExcel.disabled = false;
        copyToClipboard.disabled = false;
    }

    function addToHistory(userMessage, aiResponse) {
        const conversation = {
            user: userMessage,
            ai: aiResponse,
            timestamp: new Date().toLocaleString()
        };
        
        conversationHistory.unshift(conversation);
        if (conversationHistory.length > 10) {
            conversationHistory.pop();
        }
        
        updateHistoryDisplay();
    }

    function updateHistoryDisplay() {
        const historyContainer = document.getElementById('conversation-history');
        
        if (conversationHistory.length === 0) {
            historyContainer.innerHTML = '<div class="text-center text-muted"><small>No recent conversations</small></div>';
            return;
        }
        
        historyContainer.innerHTML = conversationHistory.map((conv, index) => `
            <div class="conversation-item mb-2 p-2 border rounded" style="cursor: pointer;" onclick="loadConversation(${index})">
                <div class="fw-bold text-truncate">${conv.user.substring(0, 30)}...</div>
                <small class="text-muted">${conv.timestamp}</small>
            </div>
        `).join('');
    }

    function exportAsPDF(content) {
        // Create a new window with the content
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>AI Assistant Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .content { line-height: 1.6; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>AI Assistant Report</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
                    <div class="content">${content}</div>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    function exportAsExcel(content) {
        // Create a simple CSV-like export
        const csvContent = `AI Assistant Report\nGenerated on: ${new Date().toLocaleString()}\n\n${content}`;
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `ai-report-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    function showToast(message, type = 'info') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Make loadConversation globally available
    window.loadConversation = function(index) {
        const conv = conversationHistory[index];
        if (conv) {
            // Clear current chat
            chatMessages.innerHTML = '';
            addMessage('user', conv.user);
            addMessage('ai', conv.ai);
            currentResponse = conv.ai;
            enableExportButtons();
        }
    };
});
</script>

<style>
.chat-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.user-message .message-content {
    text-align: left;
}

.ai-message .message-content {
    text-align: left;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}

.btn-outline-purple {
    color: #6f42c1;
    border-color: #6f42c1;
    background-color: transparent;
}

.btn-outline-purple:hover {
    color: white;
    background-color: #6f42c1;
    border-color: #6f42c1;
}
</style>
@endpush 