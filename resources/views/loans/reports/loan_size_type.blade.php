@extends('layouts.main')

@section('title', 'Loan Size Type Report')

@section('content')
<div class="page-wrapper">
  <div class="page-content">
    <h6 class="mb-0 text-uppercase">Loan Size Type Report</h6>
    <hr />

    <div class="card mb-3">
      <div class="card-body">
        @if(isset($company))
        <div class="row mb-3 align-items-center">
          <div class="col-md-8 d-flex align-items-center">
            <div>
              <h5 class="mb-1">{{ $company->name ?? 'Company' }}</h5>
              <small class="text-muted">TIN: {{ $company->tin ?? '-' }} | Phone: {{ $company->phone ?? '-' }}</small><br>
              <small class="text-muted">Address: {{ $company->address ?? '-' }}</small>
              <small class="text-muted">Email: {{ $company->email ?? '-' }}</small>
            </div>
          </div>
          <div class="col-md-4 text-md-end">
            <small class="text-muted">Period: {{ ($startDate && $endDate) ? ($startDate.' - '.$endDate) : 'All Time' }}</small>
          </div>
        </div>
        @endif
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}" />
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select class="form-select" name="branch_id">
              <option value="all">All Branches</option>
              @foreach(auth()->user()->branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id')==$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2"><i class="bx bx-search"></i> Filter</button>
            <a href="{{ route('reports.loan-size-type.export', request()->all()) }}" class="btn btn-outline-success me-2"><i class="bx bx-file"></i> Export Excel</a>
            <a href="{{ route('reports.loan-size-type.export-pdf', request()->all()) }}" class="btn btn-outline-danger"><i class="bx bx-file-pdf"></i> Export PDF</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-light">
              <tr>
                <th>LOAN SIZE TYPE</th>
                <th class="text-end">NO. OF LOAN</th>
                <th class="text-end">LOAN AMOUNT</th>
                <th class="text-end">INTEREST</th>
                <th class="text-end">TOTAL LOAN</th>
                <th class="text-end">TOTAL LOAN OUTSTANDING</th>
                <th class="text-end">NO. OF LOANS IN ARREARS</th>
                <th class="text-end">TOTAL ARREARS AMOUNT</th>
                <th class="text-end">NO. OF LOANS IN DELAYED</th>
                <th class="text-end">DELAYED AMOUNT</th>
                <th class="text-end">OUTSTANDING IN DELAYED</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $r)
                <tr>
                  <td>{{ $r['label'] }}</td>
                  <td class="text-end">{{ number_format($r['count']) }}</td>
                  <td class="text-end">{{ number_format($r['loan_amount'], 2) }}</td>
                  <td class="text-end">{{ number_format($r['interest'], 2) }}</td>
                  <td class="text-end">{{ number_format($r['total_loan'], 2) }}</td>
                  <td class="text-end">{{ number_format($r['total_outstanding'], 2) }}</td>
                  <td class="text-end">{{ number_format($r['arrears_count']) }}</td>
                  <td class="text-end">{{ number_format($r['arrears_amount'], 2) }}</td>
                  <td class="text-end">{{ number_format($r['delayed_count']) }}</td>
                  <td class="text-end">{{ number_format($r['delayed_amount'], 2) }}</td>
                  <td class="text-end">{{ number_format($r['outstanding_in_delayed'], 2) }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-secondary fw-bold">
              <tr>
                <td>GRAND TOTAL</td>
                <td class="text-end">{{ number_format($grand['count']) }}</td>
                <td class="text-end">{{ number_format($grand['loan_amount'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['interest'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['total_loan'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['total_outstanding'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['arrears_count']) }}</td>
                <td class="text-end">{{ number_format($grand['arrears_amount'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['delayed_count']) }}</td>
                <td class="text-end">{{ number_format($grand['delayed_amount'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['outstanding_in_delayed'], 2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


