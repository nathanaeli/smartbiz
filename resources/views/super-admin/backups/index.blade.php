@extends('layouts.super-admin')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20 me-2">
                                <path opacity="0.4" d="M21.5 14.0784V16.8784C21.5 18.0493 20.9493 18.8784 19.8284 18.8784H18.523C18.3026 19.3151 17.8576 19.6284 17.273 19.6284H15.848C15.3349 19.6284 14.8909 19.3151 14.6705 18.8784H4.17157C3.05075 18.8784 2.5 18.0493 2.5 16.8784V14.0784C2.5 12.9075 3.05075 12.0784 4.17157 12.0784H19.8284C20.9493 12.0784 21.5 12.9075 21.5 14.0784Z" fill="currentColor"></path>
                                <path d="M7.1537 17.3151H15.8473C16.3151 17.3151 16.6949 16.9353 16.6949 16.4675V14.571C16.6949 14.1032 16.3151 13.7234 15.8473 13.7234H7.1537C6.68588 13.7234 6.30605 14.1032 6.30605 14.571V16.4675C6.30605 16.9353 6.68588 17.3151 7.1537 17.3151Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5007 7.79614C10.5007 6.16538 11.8699 4.79614 13.5007 4.79614C15.1314 4.79614 16.5007 6.16538 16.5007 7.79614C16.5007 8.18102 16.4307 8.54836 16.3027 8.89036C17.6047 9.16836 18.5007 10.2694 18.5007 11.6344V13.4644C18.5007 13.9814 18.2147 14.4644 17.7507 14.4644H9.25065C8.78665 14.4644 8.50065 13.9814 8.50065 13.4644V11.6344C8.50065 10.2694 9.39665 9.16836 10.6987 8.89036C10.5707 8.54836 10.5007 8.18102 10.5007 7.79614ZM13.5007 6.29614C12.6757 6.29614 12.0007 6.97114 12.0007 7.79614C12.0007 8.62114 12.6757 9.29614 13.5007 9.29614C14.3257 9.29614 15.0007 8.62114 15.0007 7.79614C15.0007 6.97114 14.3257 6.29614 13.5007 6.29614Z" fill="currentColor"></path>
                            </svg>
                            Database Backups
                        </h4>
                        <small class="text-white-50">Create, manage and download database backups</small>
                    </div>
                    <form method="POST" action="{{ route('super-admin.backups.create') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                             <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                 <path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                             </svg>
                             Create Backup
                         </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white h-100">
                             <div class="card-body text-center">
                                 <svg width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-32 mb-2">
                                     <path opacity="0.4" d="M21.5 14.0784V16.8784C21.5 18.0493 20.9493 18.8784 19.8284 18.8784H18.523C18.3026 19.3151 17.8576 19.6284 17.273 19.6284H15.848C15.3349 19.6284 14.8909 19.3151 14.6705 18.8784H4.17157C3.05075 18.8784 2.5 18.0493 2.5 16.8784V14.0784C2.5 12.9075 3.05075 12.0784 4.17157 12.0784H19.8284C20.9493 12.0784 21.5 12.9075 21.5 14.0784Z" fill="currentColor"></path>
                                     <path d="M7.1537 17.3151H15.8473C16.3151 17.3151 16.6949 16.9353 16.6949 16.4675V14.571C16.6949 14.1032 16.3151 13.7234 15.8473 13.7234H7.1537C6.68588 13.7234 6.30605 14.1032 6.30605 14.571V16.4675C6.30605 16.9353 6.68588 17.3151 7.1537 17.3151Z" fill="currentColor"></path>
                                     <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5007 7.79614C10.5007 6.16538 11.8699 4.79614 13.5007 4.79614C15.1314 4.79614 16.5007 6.16538 16.5007 7.79614C16.5007 8.18102 16.4307 8.54836 16.3027 8.89036C17.6047 9.16836 18.5007 10.2694 18.5007 11.6344V13.4644C18.5007 13.9814 18.2147 14.4644 17.7507 14.4644H9.25065C8.78665 14.4644 8.50065 13.9814 8.50065 13.4644V11.6344C8.50065 10.2694 9.39665 9.16836 10.6987 8.89036C10.5707 8.54836 10.5007 8.18102 10.5007 7.79614ZM13.5007 6.29614C12.6757 6.29614 12.0007 6.97114 12.0007 7.79614C12.0007 8.62114 12.6757 9.29614 13.5007 9.29614C14.3257 9.29614 15.0007 8.62114 15.0007 7.79614C15.0007 6.97114 14.3257 6.29614 13.5007 6.29614Z" fill="currentColor"></path>
                                 </svg>
                                 <h5>{{ number_format($stats['total_backups']) }}</h5>
                                 <small>Total Backups</small>
                             </div>
                         </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white h-100">
                             <div class="card-body text-center">
                                 <svg width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-32 mb-2">
                                     <path d="M2 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10S2 17.52 2 12zm10-8c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm-1 13h2v-6h-2v6zm0-8h2V7h-2v2z" fill="currentColor"></path>
                                 </svg>
                                 <h5>{{ number_format($stats['total_size'] / 1024 / 1024, 1) }} MB</h5>
                                 <small>Total Size</small>
                             </div>
                         </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white h-100">
                             <div class="card-body text-center">
                                 <svg width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-32 mb-2">
                                     <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z" fill="currentColor"></path>
                                 </svg>
                                 <h6 class="mb-0">
                                     @if($stats['newest_backup'])
                                         {{ \Carbon\Carbon::parse($stats['newest_backup'])->format('M d, Y') }}
                                     @else
                                         No backups
                                     @endif
                                 </h6>
                                 <small>Latest Backup</small>
                             </div>
                         </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white h-100">
                             <div class="card-body text-center">
                                 <svg width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-32 mb-2">
                                     <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2z" fill="currentColor"></path>
                                 </svg>
                                 <h6 class="mb-0">
                                     @if($stats['oldest_backup'])
                                         {{ \Carbon\Carbon::parse($stats['oldest_backup'])->format('M d, Y') }}
                                     @else
                                         No backups
                                     @endif
                                 </h6>
                                 <small>Oldest Backup</small>
                             </div>
                         </div>
                    </div>
                </div>

                <!-- Backup Information -->
                <div class="alert alert-info">
                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-2">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"></path>
                    </svg>
                    <strong>Backup Information:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Backups are automatically sent to: <strong>petsonvedastuskisenya1997@gmail.com</strong></li>
                        <li>Backup files are stored in the <code>public/backups/</code> folder for easy access</li>
                        <li>Database backups include all tables and data in SQL format</li>
                        <li>SQL files can be imported directly into MySQL/MariaDB databases</li>
                    </ul>
                </div>

                <!-- Backups Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>
                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="currentColor"></path>
                                    </svg>
                                    SQL Backup File
                                </th>
                                <th>
                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z" fill="currentColor"></path>
                                    </svg>
                                    Date Created
                                </th>
                                <th>
                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                        <path d="M2 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10S2 17.52 2 12zm10-8c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm-1 13h2v-6h-2v6zm0-8h2V7h-2v2z" fill="currentColor"></path>
                                    </svg>
                                    Size
                                </th>
                                <th>
                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" fill="currentColor"></path>
                                        <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"></path>
                                    </svg>
                                    Age
                                </th>
                                <th>
                                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                        <path d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4V7zm-1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 11.99 2zM12 20c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"></path>
                                    </svg>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20 text-info me-3">
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="currentColor"></path>
                                        </svg>
                                        <div>
                                            <strong>{{ basename($backup->path) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $backup->path }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $backup->date->format('M d, Y H:i:s') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ number_format($backup->size / 1024 / 1024, 2) }} MB
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $backup->date->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('super-admin.backups.download', basename($backup->path)) }}"
                                           class="btn btn-sm btn-primary" title="Download">
                                            <svg width="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-14">
                                                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" fill="currentColor"></path>
                                            </svg>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                                onclick="confirmDelete('{{ basename($backup->path) }}')"
                                                title="Delete">
                                            <svg width="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-14">
                                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <svg width="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-48 text-muted mb-3">
                                            <path opacity="0.4" d="M21.5 14.0784V16.8784C21.5 18.0493 20.9493 18.8784 19.8284 18.8784H18.523C18.3026 19.3151 17.8576 19.6284 17.273 19.6284H15.848C15.3349 19.6284 14.8909 19.3151 14.6705 18.8784H4.17157C3.05075 18.8784 2.5 18.0493 2.5 16.8784V14.0784C2.5 12.9075 3.05075 12.0784 4.17157 12.0784H19.8284C20.9493 12.0784 21.5 12.9075 21.5 14.0784Z" fill="currentColor"></path>
                                            <path d="M7.1537 17.3151H15.8473C16.3151 17.3151 16.6949 16.9353 16.6949 16.4675V14.571C16.6949 14.1032 16.3151 13.7234 15.8473 13.7234H7.1537C6.68588 13.7234 6.30605 14.1032 6.30605 14.571V16.4675C6.30605 16.9353 6.68588 17.3151 7.1537 17.3151Z" fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5007 7.79614C10.5007 6.16538 11.8699 4.79614 13.5007 4.79614C15.1314 4.79614 16.5007 6.16538 16.5007 7.79614C16.5007 8.18102 16.4307 8.54836 16.3027 8.89036C17.6047 9.16836 18.5007 10.2694 18.5007 11.6344V13.4644C18.5007 13.9814 18.2147 14.4644 17.7507 14.4644H9.25065C8.78665 14.4644 8.50065 13.9814 8.50065 13.4644V11.6344C8.50065 10.2694 9.39665 9.16836 10.6987 8.89036C10.5707 8.54836 10.5007 8.18102 10.5007 7.79614ZM13.5007 6.29614C12.6757 6.29614 12.0007 6.97114 12.0007 7.79614C12.0007 8.62114 12.6757 9.29614 13.5007 9.29614C14.3257 9.29614 15.0007 8.62114 15.0007 7.79614C15.0007 6.97114 14.3257 6.29614 13.5007 6.29614Z" fill="currentColor"></path>
                                        </svg>
                                        <h5 class="text-muted">No SQL Backups Found</h5>
                                        <p class="text-muted">Create your first database backup to get started with data protection.</p>
                                        <form method="POST" action="{{ route('super-admin.backups.create') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">
                                                <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                                                    <path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                                Create First SQL Backup
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Public Backups Section -->
                @php
                    $publicBackupsPath = public_path('backups');
                    $publicBackups = [];

                    if (file_exists($publicBackupsPath)) {
                        $files = scandir($publicBackupsPath);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                                $filePath = $publicBackupsPath . '/' . $file;
                                $publicBackups[] = [
                                    'name' => $file,
                                    'path' => $filePath,
                                    'size' => filesize($filePath),
                                    'modified' => filemtime($filePath)
                                ];
                            }
                        }
                        // Sort by modification time (newest first)
                        usort($publicBackups, function($a, $b) {
                            return $b['modified'] - $a['modified'];
                        });
                    }
                @endphp

                @if(!empty($publicBackups))
                <div class="mt-5">
                    <h5 class="text-primary mb-3">
                        <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20 me-2">
                            <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" fill="currentColor"></path>
                        </svg>
                        Public SQL Backup Files
                    </h5>
                    <div class="alert alert-success">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-2">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"></path>
                        </svg>
                        These SQL backup files are accessible via public URLs and have been sent to your email.
                        Use them to restore your database with: <code>mysql -u username -p database_name < backup.sql</code>
                    </div>

                    <div class="row">
                        @foreach($publicBackups as $backup)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <svg width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-32 text-info me-3">
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="currentColor"></path>
                                        </svg>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ Str::limit($backup['name'], 25) }}</h6>
                                            <small class="text-muted">
                                                {{ number_format($backup['size'] / 1024 / 1024, 2) }} MB • SQL Database Dump
                                            </small>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            Created: {{ \Carbon\Carbon::createFromTimestamp($backup['modified'])->format('M d, Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ url('backups/' . $backup['name']) }}"
                                           class="btn btn-sm btn-outline-primary flex-fill" target="_blank">
                                            <svg width="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-14 me-1">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="m15 3 6 6m0-6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            Public Link
                                        </a>
                                        <a href="{{ route('super-admin.backups.download', $backup['name']) }}"
                                           class="btn btn-sm btn-primary">
                                            <svg width="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-14">
                                                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z" fill="currentColor"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20 me-2">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    Delete Backup
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this backup file?</p>
                <p class="text-danger mb-0"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-16 me-1">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"></path>
                        </svg>
                        Delete Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}
.empty-state {
    padding: 40px 20px;
}
.card {
    border: none;
    border-radius: 10px;
}
</style>

<script>
function confirmDelete(filename) {
    document.getElementById('deleteForm').action = '{{ url("/super-admin/backups/delete") }}/' + filename;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
