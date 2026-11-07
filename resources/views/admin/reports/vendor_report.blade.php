<!DOCTYPE html>
<html>
<head>
    {{-- 1. FIX: Added Meta Charset for Special Symbols (★) --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>Vendor Performance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* 2. FIX: Ensure DejaVu Sans is used for the star symbol */
        .badge-top-seller {
            font-family: 'DejaVu Sans', sans-serif !important;
            vertical-align: text-bottom;
            line-height: 1;
        }

        body { font-family: sans-serif; font-size: 14px; color: #212529; background: #fff; }
        .report-container { width: 100%; max-width: 900px; margin: 0 auto; padding: 20px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; margin-top: 10px; }
        th, td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: left; vertical-align: middle; }
        th { background-color: #212529; color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 12px; }

        /* Utility */
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .small { font-size: 11px; }
        .mb-0 { margin-bottom: 0; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mb-5 { margin-bottom: 3rem; }
        .text-decoration-line-through { text-decoration: line-through; }

        /* Vendor Header Box */
        .vendor-header { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 15px; border-radius: 5px; }

        /* Badges */
        .badge { padding: 4px 8px; font-size: 11px; font-weight: 700; color: #fff; border-radius: 0.25rem; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-danger { background-color: #dc3545; color: #fff; }

        /* PDF Specifics */
        @media print {
            .no-print { display: none !important; }
            .report-container { width: 100%; max-width: 100%; padding: 0; }

            /* Reduce whitespace for PDF */
            .mb-5 { margin-bottom: 1.5rem !important; }
        }

        /* 3. FIX: Removed 'page-break-inside: avoid' */
        /* This allows long vendor lists to flow naturally to the next page */
        .vendor-section {
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="report-container">

    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1>Vendor Performance Report</h1>
                <p class="text-muted">Generated on: {{ now()->format('M d, Y H:i') }}</p>

                {{-- Show what we are searching for in the PDF header --}}
                @if(isset($search) && $search)
                    <p class="badge bg-secondary text-white fs-6">Filtered by: "{{ $search }}"</p>
                @endif
            </div>

            @if(!isset($isPdf))
            <div class="d-flex gap-2 no-print">
                <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print View</button>

                {{-- Pass the 'search' param to the download route --}}
                <a href="{{ route('admin.reports.vendors.download', ['search' => request('search')]) }}" class="btn btn-primary">
                    ⬇️ Download PDF
                </a>
            </div>
            @endif
        </div>

        {{-- SEARCH FORM (Hidden in PDF) --}}
        @if(!isset($isPdf))
        <div class="card bg-light no-print">
            <div class="card-body py-3">
                <form action="{{ route('admin.reports.vendors') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search specific vendor by name..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-dark">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.reports.vendors') }}" class="btn btn-outline-danger">Reset</a>
                    @endif
                </form>
            </div>
        </div>
        @endif
    </div>

    @if($vendors->isEmpty())
        <div class="alert alert-warning text-center">
            No vendors found matching "{{ $search }}".
        </div>
    @else
        @foreach($vendors as $vendor)
        <div class="vendor-section">

            <div class="vendor-header">
                <h4 class="mb-0">{{ $vendor->name }}</h4>
                <div class="text-muted small">
                    Email: {{ $vendor->email }} <br>
                    Vendor Since: {{ $vendor->created_at->format('M d, Y') }} <br>
                    Total Listed Items: {{ $vendor->products->count() }}
                </div>
            </div>

            @if($vendor->products->isEmpty())
                <p class="text-muted ms-2">No items listed by this vendor.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%">Item Name</th>
                            <th style="width: 15%">Date Listed</th>
                            <th style="width: 20%">Price Info</th>
                            <th style="width: 15%" class="text-center">Total Sold</th>
                            <th style="width: 15%" class="text-center">Stock Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendor->sorted_products as $product)
                        <tr>
                            <td>
                                {{ $product->name }}
                                @if($loop->first && $product->total_sold > 0)
                                    {{-- The font-family DejaVu Sans + the Meta Tag above handles the star --}}
                                    <span class="badge bg-warning ms-1 badge-top-seller">★ Top Seller</span>
                                @endif
                            </td>
                            <td>{{ $product->created_at->format('Y-m-d') }}</td>

                            <td>
                                @if($product->discount > 0)
                                    <strong>${{ number_format($product->final_price, 2) }}</strong>
                                    <br>
                                    <span class="text-muted small text-decoration-line-through">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                    <span class="badge bg-danger ms-1">-{{ $product->discount }}%</span>
                                @else
                                    ${{ number_format($product->price, 2) }}
                                @endif
                            </td>

                            <td class="text-center">
                                @if($product->total_sold > 0)
                                    <strong>{{ $product->total_sold }}</strong> units
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">{{ $product->stock }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endforeach
    @endif

</div>

</body>
</html>
