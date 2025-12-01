@extends('layouts.main')

@section('title', 'Balance Sheet')

@section('content')
<div class="page-wrapper">
  <div class="page-content">
    <h6 class="mb-0 text-uppercase">BALANCE SHEET</h6>
    <small class="text-muted">As of {{ \Carbon\Carbon::parse($asOf)->format('d-m-Y') }}</small>
    <hr />

    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">As of</label>
            <input type="date" name="as_of" class="form-control" value="{{ $asOf }}" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Comparative As of</label>
            <input type="date" name="comparative_as_of" class="form-control" value="{{ $comparativeAsOf ?? '' }}" />
          </div>
          <div class="col-md-12">
            <label class="form-label d-flex justify-content-between align-items-center">
              <span>Additional Comparative Dates</span>
              <button type="button" class="btn btn-sm btn-outline-primary" id="addComparativeBtn"><i class="bx bx-plus"></i> Add Year</button>
            </label>
            <div id="comparativesContainer" class="row g-2">
              @if(is_array(request()->get('comparatives')))
                @foreach(request()->get('comparatives') as $c)
                  @if($c)
                  <div class="col-md-3 comparative-item">
                    <div class="input-group">
                      <input type="date" name="comparatives[]" class="form-control" value="{{ $c }}" />
                      <button class="btn btn-outline-danger remove-comparative" type="button"><i class="bx bx-x"></i></button>
                    </div>
                  </div>
                  @endif
                @endforeach
              @endif
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
              <option value="all">All Branches</option>
              @foreach(auth()->user()->branches as $b)
                <option value="{{ $b->id }}" {{ $branchId==$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Method</label>
            <select name="reporting_type" class="form-select">
              <option value="accrual" {{ $reportingType==='accrual'?'selected':'' }}>Accrual</option>
              <option value="cash" {{ $reportingType==='cash'?'selected':'' }}>Cash Basis</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="view_type" class="form-select">
              <option value="summary" {{ $viewType==='summary'?'selected':'' }}>Summary</option>
              <option value="detailed" {{ $viewType==='detailed'?'selected':'' }}>Detailed</option>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary"><i class="bx bx-search me-1"></i>Apply</button>
            <button type="button" class="btn btn-success" onclick="exportReport('pdf')">
              <i class="fas fa-file-pdf me-1"></i> Export PDF
            </button>
            <button type="button" class="btn btn-info" onclick="exportReport('excel')">
              <i class="fas fa-file-excel me-1"></i> Export Excel
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
     
            </div>

          <div class="card-body">
            
            {{-- Summary View --}}
    @if($viewType==='summary')
    <div class="card mb-4">
        <div class="card-header">Summary</div>
        <div class="card-body">
              <div class="table-responsive">
              @php
                $cols = [
                  ['label' => \Carbon\Carbon::parse($asOf)->format('Y-m-d'), 'assets' => $assetsTotal, 'liabEq' => $liabilitiesTotal + $equityTotal, 'equity' => $equityTotal, 'liab' => $liabilitiesTotal, 'pnl' => $profitLoss],
                ];
                if (!empty($comparativesData)) {
                  foreach ($comparativesData as $row) {
                    $cols[] = [
                      'label' => \Carbon\Carbon::parse($row['date'])->format('Y-m-d'),
                      'assets' => $row['assetsTotal'],
                      'liabEq' => $row['liabilitiesTotal'] + $row['equityTotal'],
                      'equity' => $row['equityTotal'],
                      'liab' => $row['liabilitiesTotal'],
                      'pnl' => $row['profitLoss'],
                    ];
                  }
                }
              @endphp
              <table class="table table-bordered align-middle mb-0">
                <thead>
                  <tr>
                    <th>Line</th>
                    @foreach($cols as $c)
                      <th class="text-end">{{ $c['label'] }}</th>
                    @endforeach
                  </tr>
                </thead>
                  <tbody>
                  {{-- Render classes dynamically from DB-backed data --}}
                  @php
                    $classOrder = ['Assets','Liabilities','Equity'];
                    $classTotals = [
                      'Assets' => $assetsTotal,
                      'Liabilities' => $liabilitiesTotal,
                      'Equity' => $equityTotal,
                    ];
                  @endphp
                  @foreach($classOrder as $className)
                    <tr>
                      <td><b>{{ $className }}</b></td>
                      @foreach($cols as $c)
                        @php
                          if ($c['label'] === \Carbon\Carbon::parse($asOf)->format('Y-m-d')) {
                            $val = $classTotals[$className] ?? 0;
                          } else {
                            $cmp = collect($comparativesData)->firstWhere('date', $c['label']);
                            if ($cmp) {
                              if ($className==='Assets') $val = $cmp['assetsTotal'];
                              elseif ($className==='Liabilities') $val = $cmp['liabilitiesTotal'];
                              else $val = $cmp['equityTotal'];
                            } else { $val = 0; }
                          }
                        @endphp
                        <td class="text-end"><b>{{ number_format($val, 2) }}</b></td>
                      @endforeach
                    </tr>
                    {{-- Groups under each class --}}
                    @if(!empty($groupTotals[$className] ?? []))
                      @foreach(($groupTotals[$className] ?? []) as $groupName => $curTotal)
                        <tr>
                          <td class="ps-4">{{ $groupName }}</td>
                          @foreach($cols as $c)
                            @php
                              if ($c['label'] === \Carbon\Carbon::parse($asOf)->format('Y-m-d')) {
                                $gval = $curTotal;
                              } else {
                                $gval = $comparativeGroupTotals[$c['label']][$className][$groupName] ?? 0;
                              }
                            @endphp
                            <td class="text-end">{{ number_format($gval, 2) }}</td>
                          @endforeach
                        </tr>
                      @endforeach
                    @endif
                  @endforeach
                  <tr>
                    <td>Profit / Loss</td>
                    @foreach($cols as $c)
                      <td class="text-end">{{ number_format($c['pnl'], 2) }}</td>
                    @endforeach
                  </tr>
                  <tr class="fw-bold">
                    <td>Total Liabilities + Equity</td>
                    @foreach($cols as $c)
                      <td class="text-end">{{ number_format($c['liabEq'], 2) }}</td>
                    @endforeach
                      </tr>
                  <tr class="table-secondary fw-bold">
                    <td></td>
                    @foreach($cols as $c)
                      <td class="text-end"></td>
                    @endforeach
                  </tr>
                  </tbody>
                </table>
              </div>
              </div>
              </div>
              @endif

    {{-- Detailed View --}}
            @if($viewType==='detailed')
    <div class="card">
        <div class="card-header">Detailed</div>
        <div class="card-body">
            <div class="row">
                {{-- Assets --}}
                <div class="col-md-12">
                    <h5>Assets</h5>
                    @php $firstComp = !empty($comparativesData) ? ($comparativesData[0] ?? null) : null; $compCount = !empty($comparativesData) ? count($comparativesData) : 0; @endphp
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">{{ \Carbon\Carbon::parse($asOf)->format('Y-m-d') }}</th>
                                @if($compCount > 0)
                                    @foreach($comparativesData as $col)
                                        <th class="text-end">{{ \Carbon\Carbon::parse($col['date'])->format('Y-m-d') }}</th>
                                        <th class="text-end">Variance</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                      <tbody>
                        @if(isset($detailed['Assets']))
                            @foreach($detailed['Assets']['groups'] as $groupName => $group)
                                <tr class="table-secondary"><td colspan="{{ 2 + ($compCount * 2) }}">{{ $groupName }}</td></tr>
                                @foreach($group['accounts'] as $acc)
                                <tr>
                                    <td>{{ $acc['account_name'] }}</td>
                                    <td class="text-end">{{ number_format($acc['balance'], 2) }}</td>
                                    @if($compCount > 0)
                                        @for($i=0;$i<$compCount;$i++)
                                            <td></td>
                                            <td></td>
                                        @endfor
                                    @endif
                                </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td>Total {{ $groupName }}</td>
                                    @php $cur = $group['total'] ?? 0; @endphp
                                    <td class="text-end">{{ number_format($cur, 2) }}</td>
                                    @if($compCount > 0)
                                        @foreach($comparativesData as $col)
                                            @php $cmp = $comparativeGroupTotals[$col['date']]['Assets'][$groupName] ?? 0; @endphp
                                            <td class="text-end">{{ number_format($cmp, 2) }}</td>
                                            <td class="text-end">{{ number_format($cur - $cmp, 2) }}</td>
                                        @endforeach
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                        <tr class="fw-bold">
                            <td>Total Assets</td>
                            <td class="text-end">{{ number_format($assetsTotal, 2) }}</td>
                            @if($compCount > 0)
                                @foreach($comparativesData as $col)
                                    <td class="text-end">{{ number_format($col['assetsTotal'], 2) }}</td>
                                    <td class="text-end">{{ number_format($assetsTotal - $col['assetsTotal'], 2) }}</td>
                                @endforeach
                            @endif
                        </tr>
                        </tbody>
                    </table>
                </div>

                    <h5>Liabilities</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">{{ \Carbon\Carbon::parse($asOf)->format('Y-m-d') }}</th>
                                @if($compCount > 0)
                                    @foreach($comparativesData as $col)
                                        <th class="text-end">{{ \Carbon\Carbon::parse($col['date'])->format('Y-m-d') }}</th>
                                        <th class="text-end">Variance</th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($detailed['Liabilities']) && count($detailed['Liabilities']['groups']) > 0)
                            @foreach($detailed['Liabilities']['groups'] as $groupName => $group)
                                <tr class="table-secondary"><td colspan="{{ 2 + ($compCount * 2) }}">{{ $groupName }}</td></tr>
                                @foreach($group['accounts'] as $acc)
                                    <tr>
                                        <td>{{ $acc['account_name'] }}</td>
                                        <td class="text-end">{{ number_format($acc['balance'], 2) }}</td>
                                        @if($compCount > 0)
                                            @for($i=0;$i<$compCount;$i++)
                                                <td></td>
                                                <td></td>
                                            @endfor
                                        @endif
                                    </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td>Total {{ $groupName }}</td>
                                    @php $cur = $group['total'] ?? 0; @endphp
                                    <td class="text-end">{{ number_format($cur, 2) }}</td>
                                    @if($compCount > 0)
                                        @foreach($comparativesData as $col)
                                            @php $cmp = $comparativeGroupTotals[$col['date']]['Liabilities'][$groupName] ?? 0; @endphp
                                            <td class="text-end">{{ number_format($cmp, 2) }}</td>
                                            <td class="text-end">{{ number_format($cur - $cmp, 2) }}</td>
                                        @endforeach
                                    @endif
                          </tr>
                        @endforeach
                        @else
                            <tr>
                                <td>No Liabilities</td>
                                <td class="text-end">0.00</td>
                                @if($compCount > 0)
                                    @for($i=0;$i<$compCount;$i++)
                                        <td></td>
                                        <td></td>
                                    @endfor
                                @endif
                            </tr>
                        @endif
                        <tr class="fw-bold">
                            <td>Total Liabilities</td>
                            <td class="text-end">{{ number_format($liabilitiesTotal, 2) }}</td>
                            @if($compCount > 0)
                                @foreach($comparativesData as $col)
                                    <td class="text-end">{{ number_format($col['liabilitiesTotal'], 2) }}</td>
                                    <td class="text-end">{{ number_format($liabilitiesTotal - $col['liabilitiesTotal'], 2) }}</td>
                      @endforeach
                            @endif
                        </tr>
                      </tbody>
                    </table>
                
                    <h5>Equity</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">{{ \Carbon\Carbon::parse($asOf)->format('Y-m-d') }}</th>
                                @if($firstComp)
                                    <th class="text-end">{{ \Carbon\Carbon::parse($firstComp['date'])->format('Y-m-d') }}</th>
                                    <th class="text-end">Variance</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($detailed['Equity']))
                            @foreach($detailed['Equity']['groups'] as $groupName => $group)
                                <tr class="table-secondary"><td colspan="{{ $firstComp ? 4 : 2 }}">{{ $groupName }}</td></tr>
                                @foreach($group['accounts'] as $acc)
                                    <tr>
                                        <td>{{ $acc['account_name'] }}</td>
                                        <td class="text-end">{{ number_format($acc['balance'], 2) }}</td>
                                        @if($firstComp)
                                            <td></td>
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td>Total {{ $groupName }}</td>
                                    @php $cur = $group['total'] ?? 0; $cmp = $firstComp ? ($comparativeGroupTotals[$firstComp['date']]['Equity'][$groupName] ?? 0) : null; @endphp
                                    <td class="text-end">{{ number_format($cur, 2) }}</td>
                                    @if($firstComp)
                                        <td class="text-end">{{ number_format($cmp, 2) }}</td>
                                        <td class="text-end">{{ number_format(($cur - ($cmp ?? 0)), 2) }}</td>
                @endif
                                </tr>
              @endforeach
            @endif
                        <tr>
                            <td>Profit / Loss</td>
                            <td class="text-end">{{ number_format($profitLoss, 2) }}</td>
                            @if($firstComp)
                                <td class="text-end">{{ number_format($firstComp['profitLoss'], 2) }}</td>
                                <td class="text-end">{{ number_format($profitLoss - $firstComp['profitLoss'], 2) }}</td>
                            @endif
                        </tr>
                        <tr class="fw-bold">
                            <td>Total Equity</td>
                            <td class="text-end">{{ number_format($equityTotal, 2) }}</td>
                            @if($firstComp)
                                <td class="text-end">{{ number_format($firstComp['equityTotal'], 2) }}</td>
                                <td class="text-end">{{ number_format($equityTotal - $firstComp['equityTotal'], 2) }}</td>
                            @endif
                        </tr>
                        <tr class="table-secondary fw-bold">
                            <td>Total Assets</td>
                            <td class="text-end">{{ number_format($assetsTotal, 2) }}</td>
                            @if($firstComp)
                                <td class="text-end">{{ number_format($firstComp['assetsTotal'], 2) }}</td>
                                <td class="text-end">{{ number_format($assetsTotal - $firstComp['assetsTotal'], 2) }}</td>
                            @endif
                        </tr>
                        <tr class="table-secondary fw-bold">
                            <td>Total Liabilities + Equity</td>
                            <td class="text-end">{{ number_format($liabilitiesTotal + $equityTotal, 2) }}</td>
                            @if($firstComp)
                                <td class="text-end">{{ number_format($firstComp['liabilitiesTotal'] + $firstComp['equityTotal'], 2) }}</td>
                                <td class="text-end">{{ number_format(($liabilitiesTotal + $equityTotal) - ($firstComp['liabilitiesTotal'] + $firstComp['equityTotal']), 2) }}</td>
                            @endif
                        </tr>
                        </tbody>
                    </table>
      </div>
    </div>

        </div>
      </div>
    @endif

    @if(!empty($comparativesData) && $viewType==='summary')
    <div class="card mb-4">
      <div class="card-header">Comparatives</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead>
              <tr>
                <th>As of</th>
                <th class="text-end">Total Assets</th>
                <th class="text-end">Total Liabilities + Equity</th>
                <th class="text-end">Profit / Loss</th>
              </tr>
            </thead>
            <tbody>
              @foreach($comparativesData as $row)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($row['date'])->format('Y-m-d') }}</td>
                  <td class="text-end">{{ number_format($row['assetsTotal'], 2) }}</td>
                  <td class="text-end">{{ number_format($row['liabilitiesTotal'] + $row['equityTotal'], 2) }}</td>
                  <td class="text-end">{{ number_format($row['profitLoss'], 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif
</div>

@endsection

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('addComparativeBtn');
    const container = document.getElementById('comparativesContainer');
    const form = document.querySelector('form');
    const viewTypeSelect = form ? form.querySelector('select[name="view_type"]') : null;
    // If URL has view_type=summary, reflect it in the dropdown (and vice versa)
    const urlParams = new URLSearchParams(window.location.search);
    const vtParam = (urlParams.get('view_type') || '').toLowerCase();
    if (viewTypeSelect && vtParam) {
      if (vtParam === 'summary' && viewTypeSelect.value !== 'summary') {
        viewTypeSelect.value = 'summary';
      } else if (vtParam === 'detailed' && viewTypeSelect.value !== 'detailed') {
        viewTypeSelect.value = 'detailed';
      }
    }
    if (addBtn && container) {
      addBtn.addEventListener('click', function() {
        const wrapper = document.createElement('div');
        wrapper.className = 'col-md-3 comparative-item';
        wrapper.innerHTML = '<div class="input-group">\
          <input type="date" name="comparatives[]" class="form-control" />\
          <button class="btn btn-outline-danger remove-comparative" type="button"><i class="bx bx-x"></i></button>\
        </div>';
        container.appendChild(wrapper);
      });
      container.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-comparative');
        if (btn) {
          const item = btn.closest('.comparative-item');
          if (item) item.remove();
        }
      });
    }
    // Keep URL and view selection in sync; submit the form (GET) with updated view_type
    if (form && viewTypeSelect) {
      viewTypeSelect.addEventListener('change', function() {
        // Ensure the form has the current view_type value
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'view_type';
        hidden.value = viewTypeSelect.value;
        form.appendChild(hidden);
        form.requestSubmit ? form.requestSubmit() : form.submit();
      });
    }
  });

  function exportReport(type) {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    formData.append('export_type', type);
    // Ensure current view_type is captured
    const viewTypeSelect = form.querySelector('select[name="view_type"]');
    if (viewTypeSelect) {
      formData.set('view_type', viewTypeSelect.value);
    }
    
    // Create a temporary form for export
    const tempForm = document.createElement('form');
    tempForm.method = 'GET';
    tempForm.action = '{{ route("accounting.reports.balance-sheet.export") }}';
    tempForm.style.display = 'none';
    
    // Add all form data as hidden inputs
    for (let [key, value] of formData.entries()) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      tempForm.appendChild(input);
    }
    
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
  }
</script>