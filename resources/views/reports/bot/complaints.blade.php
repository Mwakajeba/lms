@extends('layouts.main')

@section('title', 'BOT Complaints Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-transfer'],
                ['label' => 'Complaints', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">BOT Complaint Report for the Quarter Ended</h6>
            <hr />

            <style>
                .bot-container { background: #fff; padding: 16px; border: 1px solid #e5e7eb; }
                .bot-header { display: grid; grid-template-columns: 1fr 300px; gap: 12px; margin-bottom: 12px; }
                .bot-title { font-weight: 700; font-size: 14px; line-height: 1.2; }
                .bot-sub { font-size: 12px; color: #374151; }
                .bot-meta { border: 1px solid #111827; }
                .bot-meta-row { display: grid; grid-template-columns: 140px 1fr; border-bottom: 1px solid #111827; }
                .bot-meta-row:last-child { border-bottom: none; }
                .bot-meta-cell { padding: 6px 8px; font-size: 12px; }
                .bot-meta-label { background: #f3f4f6; font-weight: 600; border-right: 1px solid #111827; }
                .bot-table { width: 100%; border-collapse: collapse; font-size: 12px; }
                .bot-table th, .bot-table td { border: 1px solid #111827; padding: 6px 8px; vertical-align: top; }
                .bot-table thead th { background: #f3f4f6; font-weight: 700; text-transform: uppercase; font-size: 12px; }
                .col-sno { width: 50px; text-align: center; }
                .col-particular { width: 40%; }
                .col-num { width: 120px; text-align: right; font-weight: 600; }
                .col-nature { width: 90px; text-align: right; font-weight: 600; }
                .top-actions { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; }
            </style>

            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.bot.complaints') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">As at Date</label>
                            <input type="date" id="as_of_date" name="as_of_date" value="{{ $asOfDate ?? now()->format('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-9 top-actions">
                            <button type="submit" class="btn btn-secondary"><i class="bx bx-filter me-1"></i> Filter</button>
                            <a href="{{ route('reports.bot.complaints.export', ['as_of_date' => $asOfDate ?? now()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-download me-1"></i> Download Template (XLS)
                            </a>
                            <a href="{{ route('reports.bot') }}" class="btn btn-light"><i class="bx bx-arrow-back me-1"></i> Back</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body bot-container">
                    <div class="bot-header">
                        <div>
                            <div class="bot-title">NAME OF INSTITUTION:</div>
                            <div class="bot-sub" style="margin-top: 6px;">BOT FORM MSP2-06: To be submitted Quarterly</div>
                            <div class="bot-sub">(Amount in TZS 0.00)</div>
                        </div>
                        <div class="bot-meta">
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">MSP NAME</div>
                                <div class="bot-meta-cell">{{ auth()->user()->company->name ?? '—' }}</div>
                            </div>
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">MSP CODE</div>
                                <div class="bot-meta-cell">{{ auth()->user()->company->msp_code ?? '—' }}</div>
                            </div>
                            <div class="bot-meta-row">
                                <div class="bot-meta-cell bot-meta-label">DATE</div>
                                <div class="bot-meta-cell">{{ \Carbon\Carbon::parse($asOfDate ?? now())->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="bot-table">
                            <thead>
                                <tr>
                                    <th class="col-sno">Sno</th>
                                    <th class="col-particular">Particulars</th>
                                    <th class="col-num">Number</th>
                                    <th class="col-num">Value (TZS 0.00)</th>
                                    <th class="text-center" colspan="8">Nature of Complaints</th>
                                </tr>
                                <tr>
                                    <th colspan="4"></th>
                                    <th class="col-nature">A</th>
                                    <th class="col-nature">B</th>
                                    <th class="col-nature">C</th>
                                    <th class="col-nature">D</th>
                                    <th class="col-nature">E</th>
                                    <th class="col-nature">F</th>
                                    <th class="col-nature">G</th>
                                    <th class="col-nature">H</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($rows ?? []) as $idx => $label)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $label }}</td>
                                    <td class="col-num">0</td>
                                    <td class="col-num">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                    <td class="col-nature">0</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 