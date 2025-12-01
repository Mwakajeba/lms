@extends('layouts.main')

@section('title', 'Monthly Loan Performance')

@section('content')
<div class="page-wrapper">
  <div class="page-content">
    <h6 class="mb-0 text-uppercase">Monthly Loan Performance</h6>
    <hr />

    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
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
            <a href="{{ route('reports.monthly-performance.export', request()->all()) }}" class="btn btn-outline-success me-2"><i class="bx bx-file"></i> Excel</a>
            <a href="{{ route('reports.monthly-performance.export-pdf', request()->all()) }}" class="btn btn-outline-danger"><i class="bx bx-file-pdf"></i> PDF</a>
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
                <th>MONTH</th>
                <th class="text-end">LOAN GIVEN</th>
                <th class="text-end">INTEREST</th>
                <th class="text-end">TOTAL LOAN + INTEREST</th>
                <th class="text-end">TOTAL AMOUNT COLLECTED</th>
                <th class="text-end">OUTSTANDING</th>
                <th class="text-end">ACTUAL INTEREST COLLECTED</th>
                <th class="text-end">PERFORMANCE %</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $r)
              <tr>
                <td>{{ $r['month'] }}</td>
                <td class="text-end">{{ number_format($r['loan_given'], 2) }}</td>
                <td class="text-end">{{ number_format($r['interest'], 2) }}</td>
                <td class="text-end">{{ number_format($r['total_loan'], 2) }}</td>
                <td class="text-end">{{ number_format($r['collected'], 2) }}</td>
                <td class="text-end">{{ number_format($r['outstanding'], 2) }}</td>
                <td class="text-end">{{ number_format($r['actual_interest_collected'], 2) }}</td>
                <td class="text-end">{{ number_format($r['performance'], 2) }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="table-secondary fw-bold">
              <tr>
                <td>GRAND TOTAL</td>
                <td class="text-end">{{ number_format($grand['loan_given'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['interest'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['total_loan'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['collected'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['outstanding'], 2) }}</td>
                <td class="text-end">{{ number_format($grand['actual_interest_collected'], 2) }}</td>
                <td class="text-end">{{ number_format(($grand['total_loan']>0?($grand['collected']/$grand['total_loan']*100):0), 2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


