@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0"><i class="fas fa-telescope me-2 text-primary"></i>Telescope Entry Details</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.telescope.index') }}">Telescope</a></li>
                        <li class="breadcrumb-item active">{{ substr($entry->uuid, 0, 8) }}...</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('super-admin.telescope.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Telescope
            </a>
        </div>

        <div class="row">
            <!-- Entry Details -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Entry Information
                            </h5>
                            @php
                                $typeColors = [
                                    'request' => 'primary',
                                    'query' => 'warning',
                                    'model' => 'info',
                                    'command' => 'secondary',
                                    'job' => 'success',
                                    'event' => 'danger',
                                    'cache' => 'dark',
                                    'log' => 'light'
                                ];
                                $color = $typeColors[$entry->type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} fs-6">
                                <i class="fas fa-{{ $entry->type === 'query' ? 'database' : ($entry->type === 'model' ? 'cogs' : ($entry->type === 'request' ? 'globe' : 'circle')) }} me-1"></i>
                                {{ ucfirst($entry->type) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Entry Meta Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-fingerprint text-primary me-2"></i>
                                    <strong>UUID:</strong>
                                    <br>
                                    <code class="text-muted small">{{ $entry->uuid }}</code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-hashtag text-info me-2"></i>
                                    <strong>Sequence:</strong>
                                    <br>
                                    <span class="badge bg-secondary">{{ $entry->sequence }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    <strong>Created:</strong>
                                    <br>
                                    <span>{{ \Carbon\Carbon::parse($entry->created_at)->format('M d, Y \a\t H:i:s') }}</span>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($entry->created_at)->diffForHumans() }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-layer-group text-success me-2"></i>
                                    <strong>Batch ID:</strong>
                                    <br>
                                    <code class="text-muted small">{{ $entry->batch_id ?? 'N/A' }}</code>
                                </div>
                            </div>
                        </div>

                        @if($entry->family_hash)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-link text-danger me-2"></i>
                                    <strong>Family Hash:</strong>
                                    <br>
                                    <code class="text-muted small">{{ $entry->family_hash }}</code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-eye{{ $entry->should_display_on_index ? '' : '-slash' }} text-info me-2"></i>
                                    <strong>Display on Index:</strong>
                                    <br>
                                    <span class="badge bg-{{ $entry->should_display_on_index ? 'success' : 'secondary' }}">
                                        {{ $entry->should_display_on_index ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <hr class="my-4">

                        <!-- Content Section -->
                        <div class="content-section">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-code me-2"></i>Entry Content
                            </h6>

                            @if($entry->type === 'query')
                                <!-- Database Query Display -->
                                <div class="query-content">
                                    <div class="mb-3">
                                        <strong>SQL Query:</strong>
                                        <pre class="bg-light p-3 rounded border"><code>{{ $decodedContent['sql'] ?? 'N/A' }}</code></pre>
                                    </div>

                                    @if(isset($decodedContent['bindings']) && !empty($decodedContent['bindings']))
                                    <div class="mb-3">
                                        <strong>Bindings:</strong>
                                        <pre class="bg-light p-3 rounded border"><code>{{ json_encode($decodedContent['bindings'], JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                    @endif

                                    @if(isset($decodedContent['time']))
                                    <div class="mb-3">
                                        <strong>Execution Time:</strong>
                                        <span class="badge bg-warning">{{ $decodedContent['time'] }}ms</span>
                                    </div>
                                    @endif

                                    @if(isset($decodedContent['connection_name']))
                                    <div class="mb-3">
                                        <strong>Connection:</strong>
                                        <span class="badge bg-info">{{ $decodedContent['connection_name'] }}</span>
                                    </div>
                                    @endif
                                </div>

                            @elseif($entry->type === 'model')
                                <!-- Model Event Display -->
                                <div class="model-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Action:</strong>
                                                <span class="badge bg-primary">{{ $decodedContent['action'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Model:</strong>
                                                <code>{{ $decodedContent['model'] ?? 'N/A' }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    @if(isset($decodedContent['changes']))
                                    <div class="mb-3">
                                        <strong>Changes:</strong>
                                        <pre class="bg-light p-3 rounded border"><code>{{ json_encode($decodedContent['changes'], JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                    @endif
                                </div>

                            @elseif($entry->type === 'request')
                                <!-- HTTP Request Display -->
                                <div class="request-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Method:</strong>
                                                <span class="badge bg-{{ $decodedContent['method'] === 'GET' ? 'success' : ($decodedContent['method'] === 'POST' ? 'primary' : 'warning') }}">
                                                    {{ $decodedContent['method'] ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Status Code:</strong>
                                                <span class="badge bg-{{ ($decodedContent['response_status'] ?? 200) >= 400 ? 'danger' : 'success' }}">
                                                    {{ $decodedContent['response_status'] ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <strong>URI:</strong>
                                        <code>{{ $decodedContent['uri'] ?? 'N/A' }}</code>
                                    </div>

                                    @if(isset($decodedContent['headers']))
                                    <div class="mb-3">
                                        <strong>Headers:</strong>
                                        <pre class="bg-light p-3 rounded border"><code>{{ json_encode($decodedContent['headers'], JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                    @endif

                                    @if(isset($decodedContent['payload']))
                                    <div class="mb-3">
                                        <strong>Payload:</strong>
                                        <pre class="bg-light p-3 rounded border"><code>{{ json_encode($decodedContent['payload'], JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                    @endif

                                    @if(isset($decodedContent['response']))
                                    <div class="mb-3">
                                        <strong>Response:</strong>
                                        <pre class="bg-light p-3 rounded border"><code>{{ json_encode($decodedContent['response'], JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                    @endif
                                </div>

                            @else
                                <!-- Generic JSON Display -->
                                <div class="generic-content">
                                    <pre class="bg-light p-3 rounded border"><code>{{ json_encode($decodedContent, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Entry Statistics -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Entry Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="stat-item d-flex justify-content-between mb-2">
                            <span><i class="fas fa-tag me-1"></i>Type:</span>
                            <span class="badge bg-{{ $color }}">{{ ucfirst($entry->type) }}</span>
                        </div>
                        <div class="stat-item d-flex justify-content-between mb-2">
                            <span><i class="fas fa-hashtag me-1"></i>Sequence:</span>
                            <span>{{ $entry->sequence }}</span>
                        </div>
                        <div class="stat-item d-flex justify-content-between mb-2">
                            <span><i class="fas fa-clock me-1"></i>Age:</span>
                            <span>{{ \Carbon\Carbon::parse($entry->created_at)->diffForHumans() }}</span>
                        </div>
                        <div class="stat-item d-flex justify-content-between">
                            <span><i class="fas fa-eye me-1"></i>Display:</span>
                            <span>{{ $entry->should_display_on_index ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Raw Content -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-code me-2"></i>Raw JSON Content
                        </h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-dark text-light p-3 rounded small" style="max-height: 300px; overflow-y: auto;"><code>{{ $entry->content }}</code></pre>
                        <button class="btn btn-outline-secondary btn-sm mt-2 w-100" onclick="copyToClipboard(this.previousElementSibling.textContent)">
                            <i class="fas fa-copy me-1"></i>Copy to Clipboard
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('super-admin.telescope.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-1"></i>View All Entries
                            </a>
                            <button class="btn btn-outline-info" onclick="printContent()">
                                <i class="fas fa-print me-1"></i>Print Entry
                            </button>
                            <button class="btn btn-outline-secondary" onclick="window.history.back()">
                                <i class="fas fa-arrow-left me-1"></i>Go Back
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.meta-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}
.meta-item:last-child {
    border-bottom: none;
}
pre {
    font-size: 12px;
    line-height: 1.4;
}
.stat-item {
    padding: 5px 0;
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');

        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function printContent() {
    window.print();
}
</script>
@endsection
