@extends('layouts.main')

@section('title', 'Language Test')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('app.language_test') }}</h4>
                        
                        <div class="mb-4">
                            <strong>{{ __('app.current_language') }}:</strong> {{ app()->getLocale() }}
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>{{ __('app.test_translations') }}</h5>
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <strong>{{ __('app.welcome') }}:</strong> {{ __('app.welcome') }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>{{ __('app.dashboard') }}:</strong> {{ __('app.dashboard') }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>{{ __('app.settings') }}:</strong> {{ __('app.settings') }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>{{ __('app.users') }}:</strong> {{ __('app.users') }}
                                    </li>
                                    <li class="list-group-item">
                                        <strong>{{ __('app.roles_permissions') }}:</strong> {{ __('app.roles_permissions') }}
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>{{ __('app.language_switcher') }}</h5>
                                <div class="mb-3">
                                    @include('incs.languageSwitcher')
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    {{ __('app.language_test_info') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="bx bx-arrow-back me-1"></i> {{ __('app.back_to_dashboard') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 