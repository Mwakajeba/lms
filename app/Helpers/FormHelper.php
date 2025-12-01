<?php

if (!function_exists('prevent_double_submit')) {
    /**
     * Generate JavaScript to prevent double form submission
     */
    function prevent_double_submit($formSelector = 'form', $buttonSelector = 'button[type="submit"]', $loadingText = null)
    {
        $loadingText = $loadingText ?: __('app.processing');
        
        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('{$formSelector}');
            
            forms.forEach(form => {
                let isSubmitting = false;
                
                form.addEventListener('submit', function(e) {
                    if (isSubmitting) {
                        e.preventDefault();
                        return false;
                    }
                    
                    const submitBtn = form.querySelector('{$buttonSelector}');
                    if (submitBtn) {
                        isSubmitting = true;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class=\"bx bx-loader-alt bx-spin me-1\"></i>{$loadingText}';
                        
                        // Re-enable after 5 seconds as fallback
                        setTimeout(() => {
                            isSubmitting = false;
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                            }
                        }, 5000);
                    }
                });
            });
        });
        </script>";
    }
}

if (!function_exists('form_submit_button')) {
    /**
     * Generate a form submit button with double submit prevention
     */
    function form_submit_button($text = null, $class = 'btn btn-primary', $icon = 'bx bx-save', $loadingText = null)
    {
        $text = $text ?: __('app.save');
        $loadingText = $loadingText ?: __('app.processing');
        
        return "
        <button type=\"submit\" class=\"{$class}\" data-original-text=\"{$text}\">
            <i class=\"{$icon} me-1\"></i>{$text}
        </button>
        " . prevent_double_submit();
    }
}

if (!function_exists('ajax_form_submit')) {
    /**
     * Generate JavaScript for AJAX form submission with double submit prevention
     */
    function ajax_form_submit($formSelector = 'form', $successCallback = null, $errorCallback = null)
    {
        $successCallback = $successCallback ?: 'function(response) { 
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: response.message || "' . __('app.success') . '",
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        location.reload();
                    }
                });
            }
        }';
        
        $errorCallback = $errorCallback ?: 'function(xhr) {
            let message = "' . __('app.operation_failed') . '";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: "error",
                title: "' . __('app.error') . '",
                text: message
            });
        }';
        
        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('{$formSelector}');
            
            forms.forEach(form => {
                let isSubmitting = false;
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (isSubmitting) {
                        return false;
                    }
                    
                    const submitBtn = form.querySelector('button[type=\"submit\"]');
                    const originalText = submitBtn ? submitBtn.innerHTML : '';
                    
                    if (submitBtn) {
                        isSubmitting = true;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class=\"bx bx-loader-alt bx-spin me-1\"></i>" . __('app.processing') . "';
                    }
                    
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: form.method || 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        {$successCallback}(data);
                    })
                    .catch(error => {
                        {$errorCallback}(error);
                    })
                    .finally(() => {
                        isSubmitting = false;
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    });
                });
            });
        });
        </script>";
    }
}

if (!function_exists('confirm_delete')) {
    /**
     * Generate JavaScript for delete confirmation with double submit prevention
     */
    function confirm_delete($message = null, $title = null)
    {
        $message = $message ?: __('app.are_you_sure');
        $title = $title ?: __('app.confirm');
        
        return "
        <script>
        function confirmDelete(form, message = '{$message}') {
            Swal.fire({
                title: '{$title}',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '" . __('app.yes') . "',
                cancelButtonText: '" . __('app.cancel') . "'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Prevent double submission
                    const submitBtn = form.querySelector('button[type=\"submit\"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class=\"bx bx-loader-alt bx-spin me-1\"></i>" . __('app.deleting') . "';
                    }
                    form.submit();
                }
            });
            return false;
        }
        </script>";
    }
}

if (!function_exists('form_validation_errors')) {
    /**
     * Display form validation errors with proper styling
     */
    function form_validation_errors($errors = null)
    {
        if (!$errors) {
            $errors = session('errors');
        }
        
        if (!$errors || $errors->isEmpty()) {
            return '';
        }
        
        $html = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= '<i class="bx bx-x-circle me-2"></i>';
        $html .= '<ul class="mb-0">';
        
        foreach ($errors->all() as $error) {
            $html .= '<li>' . e($error) . '</li>';
        }
        
        $html .= '</ul>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $html .= '</div>';
        
        return $html;
    }
}

if (!function_exists('form_success_message')) {
    /**
     * Display form success message
     */
    function form_success_message($message = null)
    {
        $message = $message ?: session('success');
        
        if (!$message) {
            return '';
        }
        
        return '
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            ' . e($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (!function_exists('form_error_message')) {
    /**
     * Display form error message
     */
    function form_error_message($message = null)
    {
        $message = $message ?: session('error');
        
        if (!$message) {
            return '';
        }
        
        return '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-x-circle me-2"></i>
            ' . e($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (!function_exists('form_warning_message')) {
    /**
     * Display form warning message
     */
    function form_warning_message($message = null)
    {
        $message = $message ?: session('warning');
        
        if (!$message) {
            return '';
        }
        
        return '
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bx bx-error me-2"></i>
            ' . e($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (!function_exists('form_info_message')) {
    /**
     * Display form info message
     */
    function form_info_message($message = null)
    {
        $message = $message ?: session('info');
        
        if (!$message) {
            return '';
        }
        
        return '
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bx bx-info-circle me-2"></i>
            ' . e($message) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

if (!function_exists('form_messages')) {
    /**
     * Display all form messages (success, error, warning, info)
     */
    function form_messages()
    {
        $html = '';
        
        // Validation errors
        $html .= form_validation_errors();
        
        // Success message
        $html .= form_success_message();
        
        // Error message
        $html .= form_error_message();
        
        // Warning message
        $html .= form_warning_message();
        
        // Info message
        $html .= form_info_message();
        
        return $html;
    }
} 