{{--
    USER ASSIGNMENTS PAGE
    Task management and grading for internship participants
    Using unified layout with glassmorphism design
--}}

@extends('layouts.dashboard-unified')

@section('title', 'Penugasan & Penilaian')

@php
    $role = 'participant';
    $pageTitle = 'Tugas';

    $totalAssignments = $assignments->count();
    $sortedAssignments = $assignments->sortBy('created_at');

    $submittedCount = $assignments->filter(function($a) {
        if (!$a->submitted_at) return false;
        if ((int) $a->is_revision === 1) {
            $lastSubmission = $a->submissions ? $a->submissions->sortByDesc('submitted_at')->first() : null;
            if (!$lastSubmission || ($a->updated_at && $lastSubmission->submitted_at < $a->updated_at)) {
                return false;
            }
        }
        return true;
    })->count();

    $pendingCount = $totalAssignments - $submittedCount;
    $gradedCount = $assignments->whereNotNull('grade')->count();
    $avgGrade = $gradedCount > 0 ? round($assignments->whereNotNull('grade')->avg('grade'), 1) : null;

    // Prepare data for JS popup
    $assignmentsJson = $sortedAssignments->map(function($a) {
        $showBelumKumpul = false;
        if ((int) $a->is_revision === 1) {
            $lastSubmission = $a->submissions ? $a->submissions->sortByDesc('submitted_at')->first() : null;
            if (!$lastSubmission || ($a->updated_at && $lastSubmission->submitted_at < $a->updated_at)) {
                $showBelumKumpul = true;
            }
        }
        $needsSubmit = !$a->submitted_at || (int) $a->is_revision === 1;

        return [
            'id' => $a->id,
            'title' => $a->title ?? \Illuminate\Support\Str::limit($a->description, 80),
            'description' => $a->description,
            'assignment_type' => $a->assignment_type,
            'deadline' => $a->deadline ? $a->deadline->format('d M Y') : null,
            'deadline_passed' => $a->deadline ? $a->deadline->isPast() : false,
            'file_path' => $a->file_path ? \Illuminate\Support\Facades\Storage::url($a->file_path) : null,
            'grade' => $a->grade,
            'feedback' => $a->feedback,
            'submitted_at' => $a->submitted_at ? $a->submitted_at->format('d M Y H:i') : null,
            'needs_submit' => $needsSubmit,
            'is_revision' => (int) $a->is_revision === 1 && $showBelumKumpul,
            'submit_url' => route('dashboard.assignments.submit', $a->id),
        ];
    })->values();
@endphp

@push('styles')
<style>
/* ============================================
   ASSIGNMENTS PAGE STYLES
   ============================================ */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

/* Hero Section */
.page-hero {
    background: linear-gradient(135deg, #EE2E24 0%, #C41E1A 50%, #9B1B1B 100%);
    border-radius: 24px;
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    color: white;
}

.page-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: radial-gradient(ellipse, rgba(255,255,255,0.15) 0%, transparent 70%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}

.hero-text h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.hero-text p {
    font-size: 1rem;
    opacity: 0.9;
    max-width: 500px;
    margin: 0;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.3);
}

.hero-badge-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.hero-badge-text h4 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.hero-badge-text p {
    font-size: 0.8rem;
    opacity: 0.9;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1.25rem;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
.stat-icon.green { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
.stat-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
.stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }

.stat-info h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1.2;
}

.stat-info p {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
}

/* Table Card */
.table-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.table-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.table-card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-card-header .badge-count {
    background: rgba(238, 46, 36, 0.1);
    color: #EE2E24;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Assignment Table */
.assignment-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.assignment-table thead th,
.assignment-table tbody td {
    padding: 0.875rem 1rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    vertical-align: middle;
    text-align: center;
}

.assignment-table thead th {
    background: #f9fafb;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    white-space: nowrap;
}

.assignment-table tbody td {
    font-size: 0.875rem;
    color: #374151;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.assignment-table tbody tr {
    transition: background-color 0.2s;
    cursor: pointer;
}

.assignment-table tbody tr:hover {
    background-color: rgba(238, 46, 36, 0.03);
}

.assignment-table tbody tr:last-child td {
    border-bottom: none;
}

/* Title Cell */
.task-title {
    font-weight: 600;
    color: #1f2937;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Type Badge */
.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.7rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.type-badge.harian {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
}

.type-badge.proyek {
    background: rgba(139, 92, 246, 0.1);
    color: #7c3aed;
}

/* Deadline */
.deadline-text {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 0.8rem;
    font-weight: 500;
    white-space: nowrap;
    letter-spacing: 0.02em;
}

.deadline-text.overdue {
    color: #dc2626;
    font-weight: 600;
}

.deadline-text.upcoming {
    color: #d97706;
}

.deadline-text.normal {
    color: #374151;
}

/* Status Icon */
.status-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: transform 0.2s;
}

.status-icon:hover {
    transform: scale(1.15);
}

.status-icon.pending {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
}

.status-icon.submitted {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}

.status-icon.revision {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}

.status-icon.graded {
    background: rgba(139, 92, 246, 0.15);
    color: #7c3aed;
}

/* Detail Button */
.btn-detail {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.85rem;
    background: white;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-detail:hover {
    border-color: #EE2E24;
    color: #EE2E24;
    background: rgba(238, 46, 36, 0.04);
}

/* ============================================
   DETAIL POPUP OVERLAY
   ============================================ */
.popup-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 1.5rem;
}

.popup-overlay.active {
    display: flex;
}

.popup-card {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    animation: popup-in 0.25s ease-out;
}

@keyframes popup-in {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

/* Popup Header */
.popup-header {
    padding: 1.5rem 1.75rem 1.25rem;
    position: relative;
}

.popup-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.popup-close:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.popup-title {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
    padding-right: 2.5rem;
    line-height: 1.4;
}

.popup-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.popup-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.8rem;
    color: #6b7280;
}

.popup-meta-item i {
    font-size: 0.7rem;
}

.popup-meta-sep {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #d1d5db;
}

/* Popup Sections */
.popup-section {
    padding: 1.25rem 1.75rem;
    border-top: 1px solid #f1f5f9;
}

.popup-section-label {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #9ca3af;
    margin-bottom: 0.65rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.popup-section-label i {
    font-size: 0.75rem;
    color: #EE2E24;
}

.popup-desc {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.9rem;
    color: #374151;
    line-height: 1.7;
    letter-spacing: 0.01em;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.popup-desc-empty {
    font-size: 0.85rem;
    color: #9ca3af;
    font-style: italic;
}

/* File Download in popup */
.popup-file-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1rem;
    background: rgba(59, 130, 246, 0.08);
    color: #2563eb;
    border: 1.5px solid rgba(59, 130, 246, 0.2);
    border-radius: 10px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.popup-file-btn:hover {
    background: rgba(59, 130, 246, 0.15);
    color: #1d4ed8;
    transform: translateY(-1px);
}

.popup-no-file {
    font-size: 0.85rem;
    color: #9ca3af;
}

/* Grade Section in popup */
.popup-grade-row {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.popup-grade-item {
    flex: 1;
}

.popup-grade-label {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-bottom: 0.35rem;
}

.popup-grade-value {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 1.25rem;
    font-weight: 700;
    color: #EE2E24;
}

.popup-grade-value.none {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.85rem;
    font-weight: 400;
    color: #9ca3af;
}

.popup-feedback-text {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.6;
}

/* Action Section in popup */
.popup-action {
    padding: 1.25rem 1.75rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
    border-radius: 0 0 20px 20px;
}

.popup-submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.5rem;
    background: linear-gradient(135deg, #EE2E24, #C41E1A);
    color: white;
    border: none;
    border-radius: 12px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 14px rgba(238, 46, 36, 0.25);
}

.popup-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(238, 46, 36, 0.35);
}

.popup-submit-hint {
    font-size: 0.72rem;
    color: #9ca3af;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.popup-completed-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.25rem;
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-radius: 10px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
    font-size: 2rem;
    color: #9ca3af;
}

.empty-state h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn-empty-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.5rem;
    background: linear-gradient(135deg, #EE2E24, #C41E1A);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 14px rgba(238, 46, 36, 0.25);
}

.btn-empty-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(238, 46, 36, 0.35);
    color: white;
}

/* Animations */
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.page-hero { animation: fade-in 0.4s ease-out; }
.stat-card { animation: fade-in 0.5s ease-out; }
.stat-card:nth-child(2) { animation-delay: 0.1s; }
.stat-card:nth-child(3) { animation-delay: 0.15s; }
.stat-card:nth-child(4) { animation-delay: 0.2s; }
.table-card { animation: fade-in 0.6s ease-out; }

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-hero {
        padding: 1.5rem;
    }

    .hero-content {
        flex-direction: column;
        text-align: center;
    }

    .hero-text h1 {
        font-size: 1.35rem;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .stat-info h3 {
        font-size: 1.25rem;
    }

    .assignment-table thead th,
    .assignment-table tbody td {
        padding: 0.75rem 0.75rem;
        font-size: 0.78rem;
    }

    .popup-card {
        max-width: 100%;
        border-radius: 16px;
    }

    .popup-header,
    .popup-section {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    .popup-action {
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }
}

/* Scrollbar for popup */
.popup-card::-webkit-scrollbar {
    width: 6px;
}
.popup-card::-webkit-scrollbar-track {
    background: transparent;
}
.popup-card::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<div class="page-hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>
                <i class="fas fa-clipboard-check"></i>
                Penugasan & Penilaian
            </h1>
            <p>Kelola tugas dan lihat penilaian dari pembimbing Anda</p>
        </div>
        @if($pendingCount > 0)
        <div class="hero-badge">
            <div class="hero-badge-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="hero-badge-text">
                <h4>{{ $pendingCount }}</h4>
                <p>Tugas Pending</p>
            </div>
        </div>
        @elseif($totalAssignments > 0)
        <div class="hero-badge">
            <div class="hero-badge-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="hero-badge-text">
                <h4>{{ $submittedCount }}/{{ $totalAssignments }}</h4>
                <p>Tugas Selesai</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-list-check"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $totalAssignments }}</h3>
            <p>Total Tugas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-circle-check"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $submittedCount }}</h3>
            <p>Sudah Dikumpulkan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $pendingCount }}</h3>
            <p>Belum Dikumpulkan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $avgGrade !== null ? $avgGrade . '/10' : '-' }}</h3>
            <p>Rata-rata Nilai</p>
        </div>
    </div>
</div>

{{-- Assignments Table --}}
@if($totalAssignments > 0)
<div class="table-card">
    <div class="table-card-header">
        <h3>
            <i class="fas fa-tasks" style="color: #EE2E24;"></i>
            Daftar Tugas
        </h3>
        <span class="badge-count">{{ $totalAssignments }} tugas</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="assignment-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 34%;">Judul Tugas</th>
                    <th style="width: 14%;">Jenis</th>
                    <th style="width: 18%;">Deadline</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 12%;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($sortedAssignments as $assignment)
                @php
                    $showBelumKumpul = false;
                    if ($assignment->is_revision === 1) {
                        $lastSubmission = $assignment->submissions ? $assignment->submissions->sortByDesc('submitted_at')->first() : null;
                        if (!$lastSubmission || ($assignment->updated_at && $lastSubmission->submitted_at < $assignment->updated_at)) {
                            $showBelumKumpul = true;
                        }
                    }

                    $isOverdue = $assignment->deadline && \Carbon\Carbon::parse($assignment->deadline)->isPast() && !$assignment->submitted_at;
                    $isUpcoming = $assignment->deadline && \Carbon\Carbon::parse($assignment->deadline)->diffInDays(now()) <= 3 && \Carbon\Carbon::parse($assignment->deadline)->isFuture();

                    // Determine status
                    $statusClass = 'pending';
                    $statusIcon = 'fa-clock';
                    $statusTooltip = 'Belum dikumpulkan';
                    if ($assignment->grade !== null) {
                        $statusClass = 'graded';
                        $statusIcon = 'fa-star';
                        $statusTooltip = 'Sudah dinilai';
                    } elseif ($showBelumKumpul) {
                        $statusClass = 'revision';
                        $statusIcon = 'fa-redo';
                        $statusTooltip = 'Perlu revisi';
                    } elseif ($assignment->submitted_at) {
                        $statusClass = 'submitted';
                        $statusIcon = 'fa-check-circle';
                        $statusTooltip = 'Sudah dikumpulkan';
                    }

                    $needsSubmit = !$assignment->submitted_at || $assignment->is_revision === 1;
                @endphp
                <tr onclick="openDetailPopup({{ $assignment->id }})">
                    <td style="font-weight: 600; color: #6b7280;">{{ $no++ }}</td>
                    <td>
                        <div class="task-title">{{ $assignment->title ?? Str::limit($assignment->description, 60) }}</div>
                    </td>
                    <td>
                        @if($assignment->assignment_type === 'tugas_harian')
                            <span class="type-badge harian"><i class="fas fa-calendar-day"></i> Harian</span>
                        @else
                            <span class="type-badge proyek"><i class="fas fa-project-diagram"></i> Proyek</span>
                        @endif
                    </td>
                    <td>
                        @if($assignment->deadline)
                            <span class="deadline-text {{ $isOverdue ? 'overdue' : ($isUpcoming ? 'upcoming' : 'normal') }}">
                                @if($isOverdue)<i class="fas fa-exclamation-triangle"></i> @endif
                                {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M Y') }}
                            </span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="status-icon-wrap">
                            <span class="status-icon {{ $statusClass }}" title="{{ $statusTooltip }}">
                                <i class="fas {{ $statusIcon }}"></i>
                            </span>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn-detail" onclick="event.stopPropagation(); openDetailPopup({{ $assignment->id }})">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
{{-- Empty State --}}
<div class="table-card">
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        @if(isset($application) && $application && $application->status == 'accepted')
            <h4>Belum Ada Tugas</h4>
            <p>Tugas dari pembimbing akan muncul di sini. Tunggu hingga pembimbing memberikan tugas pertama Anda.</p>
        @else
            <h4>Belum Ada Tugas</h4>
            <p>Tugas akan tersedia setelah Anda diterima dalam program magang.</p>
            <a href="{{ route('dashboard.status') }}" class="btn-empty-action">
                <i class="fas fa-clipboard-list"></i> Lihat Status Pengajuan
            </a>
        @endif
    </div>
</div>
@endif

{{-- Detail Popup Overlay --}}
<div class="popup-overlay" id="detailPopup">
    <div class="popup-card" id="popupCard">
        <div class="popup-header">
            <button type="button" class="popup-close" onclick="closeDetailPopup()">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="popup-title" id="popupTitle"></h3>
            <div class="popup-meta" id="popupMeta"></div>
        </div>

        <div class="popup-section">
            <div class="popup-section-label">
                <i class="fas fa-file-alt"></i> Deskripsi
            </div>
            <div id="popupDesc"></div>
        </div>

        <div class="popup-section">
            <div class="popup-section-label">
                <i class="fas fa-paperclip"></i> File Tugas
            </div>
            <div id="popupFile"></div>
        </div>

        <div class="popup-section">
            <div class="popup-section-label">
                <i class="fas fa-chart-bar"></i> Penilaian
            </div>
            <div id="popupGrade"></div>
        </div>

        <div class="popup-action" id="popupAction"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Assignment data encoded from server
const assignmentsData = @json($assignmentsJson);

const csrfToken = '{{ csrf_token() }}';

function openDetailPopup(id) {
    const data = assignmentsData.find(a => a.id === id);
    if (!data) return;

    // Title
    document.getElementById('popupTitle').textContent = data.title || 'Tugas';

    // Meta
    const typeLabel = data.assignment_type === 'tugas_harian' ? 'Harian' : 'Proyek';
    const typeIcon = data.assignment_type === 'tugas_harian' ? 'fa-calendar-day' : 'fa-project-diagram';
    let metaHtml = `<span class="popup-meta-item"><i class="fas ${typeIcon}"></i> ${typeLabel}</span>`;
    if (data.deadline) {
        metaHtml += `<span class="popup-meta-sep"></span>`;
        metaHtml += `<span class="popup-meta-item ${data.deadline_passed ? 'style="color:#dc2626;font-weight:600;"' : ''}"><i class="fas fa-calendar"></i> ${data.deadline}</span>`;
    }
    if (data.submitted_at) {
        metaHtml += `<span class="popup-meta-sep"></span>`;
        metaHtml += `<span class="popup-meta-item" style="color:#059669;"><i class="fas fa-check"></i> Dikumpulkan ${data.submitted_at}</span>`;
    }
    document.getElementById('popupMeta').innerHTML = metaHtml;

    // Description
    const descEl = document.getElementById('popupDesc');
    if (data.description) {
        descEl.innerHTML = `<div class="popup-desc">${escapeHtml(data.description)}</div>`;
    } else {
        descEl.innerHTML = `<div class="popup-desc-empty">Tidak ada deskripsi</div>`;
    }

    // File
    const fileEl = document.getElementById('popupFile');
    if (data.file_path) {
        fileEl.innerHTML = `<a href="${data.file_path}" target="_blank" class="popup-file-btn"><i class="fas fa-download"></i> Download File Tugas</a>`;
    } else {
        fileEl.innerHTML = `<span class="popup-no-file">Tidak ada file tugas</span>`;
    }

    // Grade & Feedback
    const gradeEl = document.getElementById('popupGrade');
    if (data.grade !== null || data.feedback) {
        let gradeHtml = '';
        if (data.grade !== null) {
            gradeHtml = `
                <div class="popup-grade-item">
                    <div class="popup-grade-label">Nilai</div>
                    <div class="popup-grade-value">${data.grade}/10</div>
                </div>
            `;
        } else {
            gradeHtml = `
                <div class="popup-grade-item">
                    <div class="popup-grade-label">Nilai</div>
                    <div class="popup-grade-value none">Belum dinilai</div>
                </div>
            `;
        }

        const feedbackHtml = data.feedback
            ? `<div class="popup-grade-item"><div class="popup-grade-label">Feedback</div><div class="popup-feedback-text">${escapeHtml(data.feedback)}</div></div>`
            : `<div class="popup-grade-item"><div class="popup-grade-label">Feedback</div><div class="popup-grade-value none">Tidak ada feedback</div></div>`;

        gradeEl.innerHTML = `
            <div class="popup-grade-row">
                ${gradeHtml}
                ${feedbackHtml}
            </div>
        `;
    } else {
        gradeEl.innerHTML = `<div class="popup-grade-value none">Belum dinilai</div>`;
    }

    // Action
    const actionEl = document.getElementById('popupAction');
    if (data.needs_submit) {
        const btnLabel = data.is_revision ? '<i class="fas fa-redo"></i> Revisi Tugas' : '<i class="fas fa-upload"></i> Kumpulkan';
        actionEl.innerHTML = `
            <form action="${data.submit_url}" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="${csrfToken}">
                <label class="popup-submit-btn" style="cursor:pointer;margin:0;">
                    ${btnLabel}
                    <input type="file" name="submission_file" accept=".pdf,.doc,.docx" style="display:none;" onchange="this.closest('form').submit();" required>
                </label>
            </form>
            <div class="popup-submit-hint">
                <i class="fas fa-info-circle"></i> Format: PDF, DOC, DOCX (Maks. 2MB)
            </div>
        `;
    } else {
        actionEl.innerHTML = `
            <span class="popup-completed-badge">
                <i class="fas fa-check-circle"></i> Tugas Sudah Dikumpulkan
            </span>
        `;
    }

    // Show
    document.getElementById('detailPopup').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDetailPopup() {
    document.getElementById('detailPopup').classList.remove('active');
    document.body.style.overflow = '';
}

// Close on overlay click
document.getElementById('detailPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailPopup();
    }
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailPopup();
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
