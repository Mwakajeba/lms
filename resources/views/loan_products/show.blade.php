@extends('layouts.main')
@section('title', 'Loan Product Details')
@section('content')
<div class="container mt-4">
    <h2>Loan Product Details</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">{{ $product->name }}</h4>
            <p><strong>Type:</strong> {{ $product->type->name ?? 'N/A' }}</p>
            <p><strong>Interest Rate:</strong> {{ $product->interest_rate_min }}% - {{ $product->interest_rate_max }}%</p>
            <p><strong>Principal Range:</strong> {{ number_format($product->principal_min) }} - {{ number_format($product->principal_max) }}</p>
            <p><strong>Status:</strong> <span class="badge {{ $product->status == 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($product->status) }}</span></p>
            <p><strong>Description:</strong> {{ $product->description ?? 'N/A' }}</p>
            <p><strong>Cycle:</strong> {{ $product->cycle ?? 'N/A' }}</p>
            <p><strong>Created At:</strong> {{ $product->created_at->format('M d, Y') }}</p>
            <p><strong>Updated At:</strong> {{ $product->updated_at->format('M d, Y') }}</p>
        </div>
    </div>
    <a href="{{ route('loan-products.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection
